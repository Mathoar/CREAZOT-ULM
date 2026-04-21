<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\MessageTemplate;
use App\Entity\Reservation;
use App\Entity\SiteSettings;
use App\Service\NotificationService;
use App\Service\Sms\GsmSanitizer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private GsmSanitizer $gsmSanitizer,
    ) {}

    /**
     * Aperçu live d'un SMS : sanitization GSM-7 + calcul segments + coût estimé.
     * Body : { "body": "texte", "method": "sms"|"email" (optionnel) }
     */
    #[Route('/admin/notifications/sms-preview', methods: ['POST'])]
    public function smsPreview(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $body = (string) ($data['body'] ?? '');

        $analysis = $this->gsmSanitizer->analyze($body);

        $settings = $this->em->getRepository(SiteSettings::class)->findOneBy([]);
        $costPerUnit = $settings?->getSmsCostPerUnit();
        $costPerUnitFloat = $costPerUnit !== null ? (float) $costPerUnit : null;
        $estimatedCost = $costPerUnitFloat !== null
            ? round($analysis['segments'] * $costPerUnitFloat, 4)
            : null;

        return new JsonResponse([
            'sanitized' => $analysis['sanitized'],
            'encoding' => $analysis['encoding'],
            'length' => $analysis['length'],
            'units' => $analysis['units'],
            'segments' => $analysis['segments'],
            'replacedChars' => $analysis['replacedChars'],
            'unsupportedChars' => $analysis['unsupportedChars'],
            'costPerUnit' => $costPerUnitFloat,
            'estimatedCostPerSms' => $estimatedCost,
        ]);
    }

    /**
     * Envoie une notification groupée.
     * Body attendu : { reservationIds: [1,2,3], templateId: 5, method: "sms"|"email", body: "texte final" }
     */
    #[Route('/admin/notifications/send', methods: ['POST'])]
    public function send(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $reservationIds = $data['reservationIds'] ?? [];
        $method = $data['method'] ?? 'email';
        $body = $data['body'] ?? '';
        $templateTitle = $data['templateTitle'] ?? '';
        $clientId = $data['clientId'] ?? null;

        if (empty($reservationIds) || empty($body)) {
            return new JsonResponse(['error' => 'reservationIds et body requis'], Response::HTTP_BAD_REQUEST);
        }

        $client = $clientId ? $this->em->getRepository(Client::class)->find($clientId) : null;
        if (!$client) {
            return new JsonResponse(['error' => 'Client introuvable'], Response::HTTP_BAD_REQUEST);
        }

        if ($method === 'sms' && !$client->getHasSMS()) {
            return new JsonResponse(['error' => 'Module SMS non activé pour ce client'], Response::HTTP_FORBIDDEN);
        }

        $reservations = $this->em->getRepository(Reservation::class)->findBy(['id' => $reservationIds]);
        if (empty($reservations)) {
            return new JsonResponse(['error' => 'Aucune réservation trouvée'], Response::HTTP_NOT_FOUND);
        }

        $results = $this->notificationService->sendToGroup($reservations, $body, $method, $client, $templateTitle);

        return new JsonResponse([
            'success' => true,
            'sent' => $results['sent'],
            'failed' => $results['failed'],
            'errors' => $results['errors'],
        ]);
    }

    /**
     * Webhook Twilio delivery status.
     */
    #[Route('/webhook/twilio/status', methods: ['POST'])]
    public function twilioStatus(Request $request): Response
    {
        $messageSid = $request->request->get('MessageSid');
        $status = $request->request->get('MessageStatus');
        $to = $request->request->get('To');

        $this->logger->info('Twilio status callback', [
            'sid' => $messageSid,
            'status' => $status,
            'to' => $to,
        ]);

        if ($status === 'delivered') {
            $phone = $this->normalizePhone($to);
            $reservations = $this->em->getRepository(Reservation::class)
                ->createQueryBuilder('r')
                ->where('r.telephone LIKE :phone')
                ->andWhere('r.notificationSent = true')
                ->andWhere('r.notificationReceived IS NULL OR r.notificationReceived = false')
                ->setParameter('phone', '%' . substr($phone, -9))
                ->orderBy('r.id', 'DESC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();

            foreach ($reservations as $reservation) {
                $reservation->setNotificationReceived(true);
            }
            $this->em->flush();
        }

        return new Response('OK', Response::HTTP_OK);
    }

    #[Route('/admin/notifications/mark-billed/{clientId}', methods: ['POST'])]
    public function markBilled(int $clientId): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $client = $this->em->getRepository(Client::class)->find($clientId);
        if (!$client) {
            return new JsonResponse(['error' => 'Client introuvable'], Response::HTTP_NOT_FOUND);
        }

        $billable = $client->getSmsBillable();
        $client->setSmsCountLastBilled($client->getSmsCount());
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'billed' => $billable,
            'newBillable' => 0,
        ]);
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
