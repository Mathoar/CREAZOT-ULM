<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ConversationThread;
use App\Entity\Reservation;
use App\Repository\ConversationThreadRepository;
use App\Service\AvailabilityService;
use App\Service\ConversationManager;
use App\Service\EmailChannelService;
use App\Service\ReservationAiService;
use App\Service\ReservationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AiReservationController extends AbstractController
{
    public function __construct(
        private ReservationAiService $aiService,
        private ConversationManager $conversationManager,
        private ConversationThreadRepository $threadRepo,
        private AvailabilityService $availabilityService,
        private ReservationFactory $reservationFactory,
        private EmailChannelService $emailChannelService,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/admin/ai-reservation/webhook/email', name: 'ai_reservation_email_webhook', methods: ['POST'])]
    public function emailWebhook(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $from = $data['from'] ?? null;
        $body = $data['body'] ?? '';
        $subject = $data['subject'] ?? '';
        $name = $data['fromName'] ?? null;
        $messageId = $data['messageId'] ?? null;
        $clientId = $data['clientId'] ?? null;

        if (!$from || !$body) {
            return new JsonResponse(['error' => 'Champs "from" et "body" requis.'], 400);
        }

        $client = $clientId ? $this->em->getRepository(\App\Entity\Client::class)->find($clientId) : null;
        if (!$client) {
            return new JsonResponse(['error' => 'Client non trouvé.'], 404);
        }

        $thread = $this->conversationManager->findOrCreateThread('email', $client, $from, null, $name);

        if ($messageId) {
            $thread->setExternalConversationId($messageId);
            $this->em->flush();
        }

        $response = $this->aiService->processMessage($thread, $body);

        return new JsonResponse([
            'threadId' => $thread->getId(),
            'status' => $thread->getStatus(),
            'response' => $response,
        ]);
    }

    #[Route('/admin/ai-reservation/conversations/{id}/validate', name: 'ai_reservation_validate', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function validateReservation(int $id, Request $request): JsonResponse
    {
        $thread = $this->threadRepo->find($id);
        if (!$thread) {
            return new JsonResponse(['error' => 'Conversation non trouvée.'], 404);
        }

        $context = $thread->getAiContext() ?? [];
        $extracted = $context['extracted'] ?? [];
        $proposedSlots = $context['proposed_slots'] ?? [];
        $circuitId = $context['circuit_id'] ?? null;

        $data = json_decode($request->getContent(), true) ?? [];
        $slotIndex = $data['slotIndex'] ?? 0;

        if (empty($proposedSlots)) {
            return new JsonResponse(['error' => 'Aucun créneau proposé dans cette conversation.'], 400);
        }

        $slot = $proposedSlots[$slotIndex] ?? $proposedSlots[0];

        $debut = new \DateTime($slot['debut']);
        $fin = new \DateTime($slot['fin']);

        if ($circuitId) {
            $circuit = $this->em->getRepository(\App\Entity\Circuit::class)->find($circuitId);
        } else {
            return new JsonResponse(['error' => 'Aucun circuit associé à cette conversation.'], 400);
        }

        $client = $thread->getClient();
        $check = $this->availabilityService->isSlotAvailable($client, $circuit, $debut, $fin);
        if ($check === false) {
            return new JsonResponse(['error' => 'Ce créneau n\'est plus disponible. Veuillez en choisir un autre.'], 409);
        }

        $reservation = $this->reservationFactory->createFromAssistant(
            $client, $circuit, $debut, $check['aeronef'], $check['pilote'],
            $extracted['customer_name'] ?? $thread->getCustomerName() ?? 'Client',
            $thread->getChannel(), [
                'email' => $thread->getCustomerEmail(),
                'phone' => $thread->getCustomerPhone(),
                'quantity' => $extracted['quantity'] ?? 1,
            ]
        );

        $this->conversationManager->linkReservation($thread, $reservation);

        return new JsonResponse([
            'success' => true,
            'reservationId' => $reservation->getId(),
            'threadId' => $thread->getId(),
        ]);
    }

    #[Route('/admin/ai-reservation/conversations/{id}/cancel', name: 'ai_reservation_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function cancelConversation(int $id): JsonResponse
    {
        $thread = $this->threadRepo->find($id);
        if (!$thread) {
            return new JsonResponse(['error' => 'Conversation non trouvée.'], 404);
        }

        $this->conversationManager->cancel($thread);

        return new JsonResponse(['success' => true, 'status' => 'cancelled']);
    }

    #[Route('/admin/ai-reservation/conversations/{id}/reply', name: 'ai_reservation_reply', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminReply(int $id, Request $request): JsonResponse
    {
        $thread = $this->threadRepo->find($id);
        if (!$thread) {
            return new JsonResponse(['error' => 'Conversation non trouvée.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';
        if (!$message) {
            return new JsonResponse(['error' => 'Le message est requis.'], 400);
        }

        $client = $thread->getClient();
        $customerEmail = $thread->getCustomerEmail();
        if (!$customerEmail) {
            return new JsonResponse(['error' => 'Pas d\'email client pour répondre.'], 400);
        }

        $this->emailChannelService->sendResponse($client, $customerEmail, '', $message);
        $this->conversationManager->appendMessage($thread, 'assistant', "[Admin] {$message}");

        return new JsonResponse(['success' => true]);
    }

    #[Route('/admin/ai-reservation/stats', name: 'ai_reservation_stats', methods: ['GET'])]
    public function stats(Request $request): JsonResponse
    {
        $clientId = (int) $request->query->get('clientId', 0);
        if (!$clientId) {
            return new JsonResponse(['error' => 'clientId requis.'], 400);
        }

        $counts = $this->conversationManager->getStats($clientId);

        return new JsonResponse([
            'pending' => $counts['pending'] ?? 0,
            'analyzing' => $counts['analyzing'] ?? 0,
            'proposing' => $counts['proposing'] ?? 0,
            'awaiting_customer' => $counts['awaiting_customer'] ?? 0,
            'awaiting_club' => $counts['awaiting_club'] ?? 0,
            'confirmed' => $counts['confirmed'] ?? 0,
            'cancelled' => $counts['cancelled'] ?? 0,
            'total' => array_sum($counts),
        ]);
    }
}
