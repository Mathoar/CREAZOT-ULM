<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Service\AssistantContextBuilder;
use App\Service\VapiService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/vapi')]
#[IsGranted('OIDC_ADMIN')]
class VapiAdminController extends AbstractController
{
    public function __construct(
        private VapiService $vapiService,
        private AssistantContextBuilder $contextBuilder,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {}

    #[Route('/setup-assistant/{clientId}', name: 'vapi_setup_assistant', methods: ['POST'])]
    public function setupAssistant(int $clientId, Request $request): JsonResponse
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);
        if (!$client) {
            return new JsonResponse(['error' => 'Client non trouvé'], 404);
        }

        if (!$client->isHasVoiceAssistant()) {
            return new JsonResponse(['error' => 'Le module Assistant Vocal n\'est pas activé pour ce client.'], 403);
        }

        $serverUrl = $request->getSchemeAndHttpHost() . '/webhook/vapi/' . $clientId;
        $existingAssistantId = $client->getVapiAssistantId();

        try {
            if ($existingAssistantId) {
                $this->vapiService->updateAssistant(
                    $existingAssistantId,
                    $client,
                    $serverUrl
                );
                return new JsonResponse([
                    'success' => true,
                    'action' => 'updated',
                    'assistant_id' => $existingAssistantId,
                    'message' => "Assistant Vapi mis à jour pour {$client->getName()}.",
                ]);
            }

            $result = $this->vapiService->createAssistant(
                $client,
                $serverUrl
            );

            $newAssistantId = $result['id'] ?? null;
            if (!$newAssistantId) {
                $this->logger->error('Vapi createAssistant: pas d\'id dans la réponse', ['response' => $result]);
                return new JsonResponse([
                    'error' => 'Vapi n\'a pas retourné d\'ID d\'assistant.',
                    'vapi_response' => $result,
                ], 502);
            }

            $client->setVapiAssistantId($newAssistantId);
            $this->em->flush();

            return new JsonResponse([
                'success' => true,
                'action' => 'created',
                'assistant_id' => $newAssistantId,
                'message' => "Assistant Vapi créé pour {$client->getName()}.",
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Vapi setup error: {error}', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/delete-assistant/{clientId}', name: 'vapi_delete_assistant', methods: ['DELETE'])]
    public function deleteAssistant(int $clientId): JsonResponse
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);
        if (!$client) {
            return new JsonResponse(['error' => 'Client non trouvé'], 404);
        }

        $assistantId = $client->getVapiAssistantId();
        if (!$assistantId) {
            return new JsonResponse(['error' => 'Aucun assistant configuré pour ce client.'], 404);
        }

        try {
            $this->vapiService->deleteAssistant($assistantId);
        } catch (\Throwable $e) {
            $this->logger->warning('Vapi delete failed (may already be deleted): {error}', ['error' => $e->getMessage()]);
        }

        $client->setVapiAssistantId(null);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => "Assistant Vapi supprimé pour {$client->getName()}.",
        ]);
    }

    #[Route('/assistant-status/{clientId}', name: 'vapi_assistant_status', methods: ['GET'])]
    public function assistantStatus(int $clientId): JsonResponse
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);
        if (!$client) {
            return new JsonResponse(['error' => 'Client non trouvé'], 404);
        }

        $assistantId = $client->getVapiAssistantId();
        if (!$assistantId) {
            return new JsonResponse([
                'configured' => false,
                'message' => 'Aucun assistant configuré pour ce client.',
            ]);
        }

        try {
            $assistant = $this->vapiService->getAssistant($assistantId);
            return new JsonResponse([
                'configured' => true,
                'assistant_id' => $assistantId,
                'assistant_name' => $assistant['name'] ?? null,
                'created_at' => $assistant['createdAt'] ?? null,
                'server_url' => $assistant['serverUrl'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'configured' => true,
                'assistant_id' => $assistantId,
                'sync_error' => 'Assistant introuvable sur Vapi : ' . $e->getMessage(),
            ]);
        }
    }

    #[Route('/test-connection', name: 'vapi_test_connection', methods: ['GET'])]
    public function testConnection(): JsonResponse
    {
        try {
            $assistants = $this->vapiService->listAssistants();
            return new JsonResponse([
                'success' => true,
                'message' => 'Connexion Vapi OK.',
                'assistants_count' => is_array($assistants) ? count($assistants) : 0,
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/prompt-preview/{clientId}', name: 'vapi_prompt_preview', methods: ['GET'])]
    public function promptPreview(int $clientId): JsonResponse
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);
        if (!$client) {
            return new JsonResponse(['error' => 'Client non trouvé'], 404);
        }

        try {
            $context = $this->contextBuilder->buildContext($client);
            $voicePrompt = $this->contextBuilder->buildPrompt($context, 'voice');
            $emailPrompt = $this->contextBuilder->buildPrompt($context, 'email');

            return new JsonResponse([
                'prompt' => $voicePrompt,
                'promptEmail' => $emailPrompt,
                'context' => $context,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Prompt preview error: {error}', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
