<?php

declare(strict_types=1);

namespace App\Service\Integration;

use App\Entity\Aeronef;
use App\Entity\Client;
use App\Entity\IntegrationPattern;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
    ) {}

    /**
     * Exécute par code technique (usage interne / super_admin).
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
     * Exécute par capability — résout automatiquement le bon pattern pour ce client.
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
        $variables = $this->variableResolver->resolve($pattern, $client, $aeronef);

        $request = $this->requestBuilder->build($pattern, $variables);

        $this->logger->info('IntegrationEngine: calling {method} {url}', [
            'method' => $request['method'],
            'url' => $request['url'],
            'pattern' => $pattern->getCode(),
            'capability' => $pattern->getCapability(),
            'clientId' => $client->getId(),
        ]);

        $response = $this->httpClient->request($request['method'], $request['url'], $request['options']);
        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            $this->logger->error('IntegrationEngine: API returned {status}', [
                'status' => $statusCode,
                'body' => $response->getContent(false),
                'pattern' => $pattern->getCode(),
            ]);
            throw new \RuntimeException(sprintf('External API returned HTTP %d', $statusCode));
        }

        $rawData = $response->toArray(false);

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
                'url' => $request['url'],
                'statusCode' => $statusCode,
                'timestamp' => (new \DateTimeImmutable())->format('c'),
            ],
        ];
    }
}
