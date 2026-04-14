<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Aeronef;
use App\Entity\Client;
use App\Entity\IntegrationPattern;
use App\Service\Integration\IntegrationEngine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/integrations')]
#[IsGranted('OIDC_ADMIN')]
class IntegrationController extends AbstractController
{
    public function __construct(
        private readonly IntegrationEngine $engine,
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * Liste les capabilities disponibles avec leurs patterns, pour le formulaire client.
     * Retourne : [{ capability: "tracking", patterns: [{ id: 3, name: "Microtrak", code: "M_TRACK" }, ...] }]
     */
    #[Route('/capabilities', name: 'integration_capabilities', methods: ['GET'])]
    #[IsGranted('OIDC_ADMIN')]
    public function getCapabilities(): JsonResponse
    {
        $patterns = $this->em->getRepository(IntegrationPattern::class)->findBy(['active' => true]);

        $grouped = [];
        foreach ($patterns as $p) {
            $cap = $p->getCapability();
            if (!$cap) continue;
            if (!isset($grouped[$cap])) {
                $grouped[$cap] = ['capability' => $cap, 'requiredModule' => $p->getRequiredModule(), 'patterns' => []];
            }
            $grouped[$cap]['patterns'][] = [
                'id' => $p->getId(),
                'name' => $p->getName(),
                'code' => $p->getCode(),
            ];
        }

        return new JsonResponse(array_values($grouped));
    }

    #[Route('/entities', name: 'integration_entities', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function getEntities(): JsonResponse
    {
        $metadatas = $this->em->getMetadataFactory()->getAllMetadata();
        $result = [];

        foreach ($metadatas as $meta) {
            $shortName = (new \ReflectionClass($meta->getName()))->getShortName();
            $fields = [];

            foreach ($meta->fieldMappings as $fieldName => $mapping) {
                $type = $mapping['type'] ?? 'unknown';
                $fields[] = [
                    'id' => $fieldName,
                    'name' => "$fieldName ($type)",
                ];
            }

            usort($fields, fn($a, $b) => strcmp($a['id'], $b['id']));

            $result[] = [
                'id' => lcfirst($shortName),
                'label' => $shortName,
                'fields' => $fields,
            ];
        }

        usort($result, fn($a, $b) => strcmp($a['label'], $b['label']));

        return new JsonResponse($result);
    }

    /**
     * Exécute par code technique : POST { pattern: "microtrak_tracking", clientId: 3 }
     */
    #[Route('/execute', name: 'integration_execute', methods: ['POST'])]
    public function execute(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $patternCode = $data['pattern'] ?? null;
        $clientId = $data['clientId'] ?? $request->headers->get('X-Client-Id');
        $aeronefId = $data['aeronefId'] ?? null;

        if (!$patternCode || !$clientId) {
            return $this->json(
                ['error' => 'Missing required fields: pattern, clientId'],
                Response::HTTP_BAD_REQUEST
            );
        }

        [$client, $aeronef, $error] = $this->resolveEntities($clientId, $aeronefId);
        if ($error) return $error;

        try {
            $result = $this->engine->execute($patternCode, $client, $aeronef);
            return $this->json($result);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            return $this->json(
                ['error' => 'Integration execution failed: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Route générique par capability.
     * GET /admin/integrations/run/{capability}              → sans contexte
     * GET /admin/integrations/run/{capability}/{contextId}  → avec contexte (codeBalise, aeronefId, etc.)
     *
     * Le contextId est résolu automatiquement : recherche par codeBalise d'abord, puis par ID numérique.
     * Le moteur résout le bon pattern pour le client courant via la capability.
     */
    #[Route('/run/{capability}/{contextId?}', name: 'integration_run_capability', methods: ['GET'])]
    #[IsGranted('OIDC_USER')]
    public function runByCapability(string $capability, ?string $contextId, Request $request): JsonResponse
    {
        $clientId = $request->headers->get('X-Client-Id');
        if (!$clientId) {
            return $this->json(['error' => 'Missing X-Client-Id header'], Response::HTTP_BAD_REQUEST);
        }

        $client = $this->em->getRepository(Client::class)->find((int) $clientId);
        if (!$client) {
            return $this->json(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        $aeronef = null;
        if ($contextId) {
            $aeronef = $this->em->getRepository(Aeronef::class)->findOneBy(['codeBalise' => $contextId])
                ?? $this->em->getRepository(Aeronef::class)->find((int) $contextId);
        }

        try {
            $result = $this->engine->executeByCapability($capability, $client, $aeronef);
            return $this->json($result['normalized']);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            return $this->json(
                ['error' => 'Integration execution failed: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @return array{Client|null, Aeronef|null, JsonResponse|null}
     */
    private function resolveEntities(mixed $clientId, mixed $aeronefId): array
    {
        $client = $this->em->getRepository(Client::class)->find((int) $clientId);
        if (!$client) {
            return [null, null, $this->json(['error' => 'Client not found'], Response::HTTP_NOT_FOUND)];
        }

        $aeronef = null;
        if ($aeronefId) {
            $aeronef = $this->em->getRepository(Aeronef::class)->find((int) $aeronefId);
            if (!$aeronef) {
                return [null, null, $this->json(['error' => 'Aeronef not found'], Response::HTTP_NOT_FOUND)];
            }
        }

        return [$client, $aeronef, null];
    }

    #[Route('/patterns/{patternCode}/test', name: 'integration_test', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function test(string $patternCode, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $clientId = $data['clientId'] ?? $request->headers->get('X-Client-Id');
        $aeronefId = $data['aeronefId'] ?? null;

        if (!$clientId) {
            return $this->json(['error' => 'Missing clientId'], Response::HTTP_BAD_REQUEST);
        }

        $client = $this->em->getRepository(Client::class)->find((int) $clientId);
        if (!$client) {
            return $this->json(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        $aeronef = $aeronefId
            ? $this->em->getRepository(Aeronef::class)->find((int) $aeronefId)
            : null;

        try {
            $result = $this->engine->execute($patternCode, $client, $aeronef);
            return $this->json([
                'success' => true,
                ...$result,
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], Response::HTTP_OK);
        }
    }
}
