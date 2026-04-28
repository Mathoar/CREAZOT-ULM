<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Service\Manex\ManexBuilderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManexController extends AbstractController
{
    public function __construct(
        private readonly ManexBuilderService $builder,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/admin/manex/preview', methods: ['GET'])]
    public function preview(Request $request): Response
    {
        $client = $this->resolveClient($request);
        if (!$client) {
            return new JsonResponse(['error' => 'Client introuvable'], 400);
        }

        $html = $this->builder->preview($client);

        return new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    #[Route('/admin/manex/generate', methods: ['POST'])]
    public function generate(Request $request): JsonResponse
    {
        $client = $this->resolveClient($request);
        $user = $this->getUser();

        if (!$client || !$user instanceof User) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        $body = json_decode($request->getContent(), true) ?? [];
        $changelog = $body['changelog'] ?? null;

        $version = $this->builder->generate($client, $user, $changelog);

        return new JsonResponse([
            'id'            => $version->getId(),
            'versionNumber' => $version->getVersionNumber(),
            'generatedAt'   => $version->getGeneratedAt()->format('c'),
            'documentUrl'   => $version->getDocument()?->contentUrl,
        ]);
    }

    #[Route('/admin/manex/ensure-sections', methods: ['POST'])]
    public function ensureSections(Request $request): JsonResponse
    {
        $client = $this->resolveClient($request);
        if (!$client) {
            return new JsonResponse(['error' => 'Client introuvable'], 400);
        }

        $this->builder->ensureSections($client);

        return new JsonResponse(['status' => 'ok']);
    }

    private function resolveClient(Request $request): ?Client
    {
        $clientId = $request->headers->get('X-Client-Id');
        if (!$clientId) {
            return null;
        }
        return $this->em->getRepository(Client::class)->find((int) $clientId);
    }
}
