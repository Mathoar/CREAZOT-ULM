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
use Psr\Log\LoggerInterface;
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
        private LoggerInterface $logger,
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

        $client = $thread->getClient();
        $tz = new \DateTimeZone($client->getTimezone() ?? 'Indian/Reunion');
        $debut = new \DateTime($slot['debut'], $tz);
        $fin = new \DateTime($slot['fin'], $tz);

        if ($circuitId) {
            $circuit = $this->em->getRepository(\App\Entity\Circuit::class)->find($circuitId);
        } else {
            return new JsonResponse(['error' => 'Aucun circuit associé à cette conversation.'], 400);
        }

        $check = $this->availabilityService->isSlotAvailable($client, $circuit, $debut, $fin);
        if ($check === false) {
            return new JsonResponse(['error' => 'Ce créneau n\'est plus disponible. Veuillez en choisir un autre.'], 409);
        }

        $customerName = $extracted['customer_name'] ?? $thread->getCustomerName() ?? 'Client';

        $reservation = $this->reservationFactory->createFromAssistant(
            $client, $circuit, $debut, $check['aeronef'], $check['pilote'],
            $customerName,
            $thread->getChannel(), [
                'email' => $thread->getCustomerEmail(),
                'phone' => $thread->getCustomerPhone(),
                'quantity' => $extracted['quantity'] ?? 1,
            ]
        );

        $this->conversationManager->linkReservation($thread, $reservation);

        $this->notifyCustomerConfirmation($thread, $reservation, $circuit, $debut);

        return new JsonResponse([
            'success' => true,
            'reservationId' => $reservation->getId(),
            'threadId' => $thread->getId(),
        ]);
    }

    #[Route('/admin/ai-reservation/conversations/{id}/cancel', name: 'ai_reservation_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function cancelConversation(int $id, Request $request): JsonResponse
    {
        $thread = $this->threadRepo->find($id);
        if (!$thread) {
            return new JsonResponse(['error' => 'Conversation non trouvée.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $reason = $data['reason'] ?? null;

        $this->conversationManager->cancel($thread);

        $this->notifyCustomerCancellation($thread, $reason);

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

    // ──────────── Notifications client ────────────

    private function notifyCustomerConfirmation(
        ConversationThread $thread,
        Reservation $reservation,
        \App\Entity\Circuit $circuit,
        \DateTimeInterface $debut,
    ): void {
        $client = $thread->getClient();
        $clubName = $client->getNom() ?? $client->getName() ?? 'Notre club';
        $customerName = $thread->getCustomerName() ?? 'Client';
        $phone = $client->getPhone() ?? '';

        $dateStr = $debut->format('d/m/Y');
        $timeStr = $debut->format('H\hi');
        $circuitName = $circuit->getNom() ?? '';
        $code = $reservation->getCode();

        $message = "Bonjour {$customerName},\n\n"
            . "Nous avons le plaisir de vous confirmer votre réservation :\n\n"
            . "  Prestation : {$circuitName}\n"
            . "  Date : {$dateStr}\n"
            . "  Heure : {$timeStr}\n"
            . "  Référence : {$code}\n\n"
            . "Nous vous attendons avec impatience !\n\n"
            . "À bientôt,\n"
            . "L'équipe {$clubName}";

        if ($phone) {
            $message .= "\nTél : {$phone}";
        }

        $customerEmail = $thread->getCustomerEmail();
        if ($customerEmail) {
            $subject = "Confirmation de votre réservation — {$clubName}";
            $this->sendNotificationEmail($client, $customerEmail, $subject, $message);
        }

        $this->conversationManager->appendMessage($thread, 'system', "Notification de confirmation envoyée au client.");

        $this->logger->info('[AI-RESA] Confirmation notification sent for thread #{id}', [
            'id' => $thread->getId(),
            'email' => $customerEmail,
            'phone' => $thread->getCustomerPhone(),
        ]);
    }

    private function notifyCustomerCancellation(ConversationThread $thread, ?string $reason): void
    {
        $client = $thread->getClient();
        $clubName = $client->getNom() ?? $client->getName() ?? 'Notre club';
        $customerName = $thread->getCustomerName() ?? 'Client';
        $phone = $client->getPhone() ?? '';

        $context = $thread->getAiContext() ?? [];
        $extracted = $context['extracted'] ?? [];
        $circuitCode = $extracted['circuit_code'] ?? '';
        $date = $extracted['preferred_date'] ?? '';

        $message = "Bonjour {$customerName},\n\n"
            . "Nous avons bien reçu votre demande de réservation";

        if ($circuitCode && $date) {
            $message .= " ({$circuitCode} le {$date})";
        }

        $message .= ".\n\nMalheureusement, nous ne sommes pas en mesure de confirmer ce créneau.";

        if ($reason) {
            $message .= "\nMotif : {$reason}";
        }

        $message .= "\n\nN'hésitez pas à nous recontacter pour trouver un autre créneau qui vous conviendrait."
            . "\n\nCordialement,\nL'équipe {$clubName}";

        if ($phone) {
            $message .= "\nTél : {$phone}";
        }

        $customerEmail = $thread->getCustomerEmail();
        if ($customerEmail) {
            $subject = "Votre demande de réservation — {$clubName}";
            $this->sendNotificationEmail($client, $customerEmail, $subject, $message);
        }

        $this->conversationManager->appendMessage($thread, 'system', "Notification d'annulation envoyée au client.");

        $this->logger->info('[AI-RESA] Cancellation notification sent for thread #{id}', [
            'id' => $thread->getId(),
            'reason' => $reason,
        ]);
    }

    private function sendNotificationEmail(\App\Entity\Client $client, string $toEmail, string $subject, string $body): void
    {
        try {
            $this->emailChannelService->sendResponse($client, $toEmail, $subject, $body);
        } catch (\Throwable $e) {
            $this->logger->error('[AI-RESA] Failed to send notification email: {error}', [
                'error' => $e->getMessage(),
                'to' => $toEmail,
            ]);
        }
    }
}
