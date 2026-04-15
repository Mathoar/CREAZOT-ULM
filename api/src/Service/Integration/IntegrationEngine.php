<?php

declare(strict_types=1);

namespace App\Service\Integration;

use App\Entity\Aeronef;
use App\Entity\Client;
use App\Entity\IntegrationPattern;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IntegrationEngine
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VariableResolver $variableResolver,
        private readonly RequestBuilder $requestBuilder,
        private readonly ResponseMapper $responseMapper,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly CacheInterface $cache,
    ) {}

    /**
     * @return array{normalized: array, raw: array, meta: array}
     */
    public function execute(string $patternCode, Client $client, ?Aeronef $aeronef = null): array
    {
        $pattern = $this->em->getRepository(IntegrationPattern::class)
            ->findOneBy(['code' => $patternCode, 'active' => true]);

        if (!$pattern) {
            throw new \RuntimeException(sprintf('Pattern "%s" not found or inactive.', $patternCode));
        }

        if (!$pattern->getClients()->contains($client)) {
            throw new \RuntimeException(sprintf(
                'Client "%s" (id=%d) is not associated with pattern "%s".',
                $client->getName(),
                $client->getId(),
                $patternCode
            ));
        }

        return $this->executePattern($pattern, $client, $aeronef);
    }

    /**
     * @return array{normalized: array, raw: array, meta: array}
     */
    public function executeByCapability(string $capability, Client $client, ?Aeronef $aeronef = null): array
    {
        $patterns = $this->em->getRepository(IntegrationPattern::class)
            ->findBy(['capability' => $capability, 'active' => true]);

        if (empty($patterns)) {
            throw new \RuntimeException(sprintf('No active pattern found for capability "%s".', $capability));
        }

        $matched = null;
        foreach ($patterns as $pattern) {
            if ($pattern->getClients()->contains($client)) {
                $matched = $pattern;
                break;
            }
        }

        if (!$matched) {
            throw new \RuntimeException(sprintf(
                'Client "%s" (id=%d) has no pattern associated for capability "%s".',
                $client->getName(),
                $client->getId(),
                $capability
            ));
        }

        return $this->executePattern($matched, $client, $aeronef);
    }

    /**
     * @return array{normalized: array, raw: array, meta: array}
     */
    private function executePattern(IntegrationPattern $pattern, Client $client, ?Aeronef $aeronef): array
    {
        $cacheTtl = $pattern->getCacheTtl();

        if ($cacheTtl && $cacheTtl > 0) {
            $cacheKey = sprintf(
                'integration_%s_%d_%s',
                $pattern->getCode(),
                $client->getId(),
                $aeronef ? $aeronef->getId() : 'no_ctx'
            );

            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($pattern, $client, $aeronef, $cacheTtl) {
                $item->expiresAfter($cacheTtl);
                return $this->doExecute($pattern, $client, $aeronef);
            });
        }

        return $this->doExecute($pattern, $client, $aeronef);
    }

    private function doExecute(IntegrationPattern $pattern, Client $client, ?Aeronef $aeronef): array
    {
        $variables = $this->variableResolver->resolve($pattern, $client, $aeronef);
        $request = $this->requestBuilder->build($pattern, $variables);

        $this->logger->info('IntegrationEngine: calling {method} {url}', [
            'method' => $request['method'],
            'url' => $request['url'],
            'pattern' => $pattern->getCode(),
            'capability' => $pattern->getCapability(),
            'clientId' => $client->getId(),
        ]);

        $rawData = null;
        $usedUrl = $request['url'];
        $statusCode = 0;

        try {
            $response = $this->httpClient->request($request['method'], $request['url'], $request['options']);
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                $rawData = $response->toArray(false);
            }
        } catch (\Throwable $e) {
            $this->logger->warning('IntegrationEngine: primary URL failed: {error}', [
                'error' => $e->getMessage(),
                'url' => $request['url'],
            ]);
        }

        if ($rawData === null && $pattern->getFallbackUrlTemplate()) {
            $fallbackUrl = $this->interpolateUrl($pattern->getFallbackUrlTemplate(), $variables);
            $usedUrl = $fallbackUrl;

            $this->logger->info('IntegrationEngine: trying fallback {url}', ['url' => $fallbackUrl]);

            try {
                $fallbackOptions = $request['options'];
                $response = $this->httpClient->request($request['method'], $fallbackUrl, $fallbackOptions);
                $statusCode = $response->getStatusCode();

                if ($statusCode >= 200 && $statusCode < 300) {
                    $rawData = $response->toArray(false);
                }
            } catch (\Throwable $e) {
                $this->logger->error('IntegrationEngine: fallback also failed: {error}', [
                    'error' => $e->getMessage(),
                    'url' => $fallbackUrl,
                ]);
            }
        }

        if ($rawData === null) {
            throw new \RuntimeException(sprintf('External API returned HTTP %d (both primary and fallback failed)', $statusCode));
        }

        $normalized = $pattern->getResponseMappings()->count() > 0
            ? $this->responseMapper->map($pattern, $rawData)
            : $rawData;

        return [
            'normalized' => $normalized,
            'raw' => $rawData,
            'meta' => [
                'pattern' => $pattern->getCode(),
                'capability' => $pattern->getCapability(),
                'method' => $request['method'],
                'url' => $usedUrl,
                'statusCode' => $statusCode,
                'cached' => false,
                'timestamp' => (new \DateTimeImmutable())->format('c'),
            ],
        ];
    }

    private function interpolateUrl(string $template, array $variables): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function (array $matches) use ($variables) {
            return $variables[$matches[1]] ?? $matches[0];
        }, $template);
    }
}
