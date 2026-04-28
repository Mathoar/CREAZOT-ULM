<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Entity\SecurityEvent;
use App\EventSubscriber\SecurityEventSubscriber;
use App\Service\ClientGetter;
use App\Service\DynamicMailerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class SecurityEventController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClientGetter $clientGetter,
        private readonly DynamicMailerFactory $dynamicMailerFactory,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/admin/security-event/send-report', methods: ['POST'])]
    public function sendReport(Request $request): JsonResponse
    {
        $client = $this->resolveClient($request);
        if (!$client) {
            return new JsonResponse(['error' => 'Client introuvable'], 400);
        }

        $body = json_decode($request->getContent(), true) ?? [];
        $eventId = $body['securityEventId'] ?? null;
        $recipientEmail = $body['recipientEmail'] ?? null;
        $recipientType = $body['recipientType'] ?? 'autre';

        if (!$eventId || !$recipientEmail) {
            return new JsonResponse(['error' => 'Paramètres manquants'], 400);
        }

        if (is_string($eventId) && preg_match('/(\d+)$/', $eventId, $m)) {
            $eventId = (int) $m[1];
        }
        $securityEvent = $this->em->getRepository(SecurityEvent::class)->find($eventId);
        if (!$securityEvent) {
            return new JsonResponse(['error' => 'Événement introuvable'], 404);
        }

        $typeLabel = SecurityEventSubscriber::TYPE_LABELS[$securityEvent->getType()] ?? $securityEvent->getType();
        $pilote = $securityEvent->getPilote();
        $aeronef = $securityEvent->getAeronef();

        try {
            $pdfContent = $this->generatePdf($securityEvent, $client, $typeLabel, $pilote, $aeronef);

            $mailer = $this->dynamicMailerFactory->getMailerForClient();

            $emailBody = $this->twig->render('emails/security_event_dsac.html.twig', [
                'typeLabel' => $typeLabel,
                'client' => $client,
                'dateEvenement' => $securityEvent->getDateEvenement()?->format('d/m/Y H:i'),
                'recipientType' => $recipientType,
            ]);

            $dateStr = $securityEvent->getDateEvenement()?->format('Y-m-d') ?? date('Y-m-d');
            $filename = "CR_securite_{$typeLabel}_{$dateStr}.pdf";

            $email = (new Email())
                ->from($client->getEmailAddressSender())
                ->to($recipientEmail)
                ->subject("Compte-rendu événement sécurité — {$typeLabel} du {$securityEvent->getDateEvenement()?->format('d/m/Y')}")
                ->html($emailBody)
                ->attach($pdfContent, $filename, 'application/pdf');

            $mailer->send($email);

            $now = new \DateTime();
            if ($recipientType === 'dsac') {
                $securityEvent->setDateNotificationDGAC($now);
            } elseif ($recipientType === 'bea') {
                $securityEvent->setDateNotificationBEA($now);
            }
            $this->em->flush();

            $this->logger->info('CR sécurité envoyé', [
                'to' => $recipientEmail,
                'recipientType' => $recipientType,
                'eventId' => $eventId,
            ]);

            return new JsonResponse(['status' => 'ok', 'message' => 'Compte-rendu envoyé']);
        } catch (\Throwable $e) {
            $this->logger->error('Échec envoi CR: {error}', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    private function generatePdf(SecurityEvent $event, Client $client, string $typeLabel, $pilote, $aeronef): string
    {
        $html = $this->twig->render('pdf/security_event_report.html.twig', [
            'event' => $event,
            'client' => $client,
            'typeLabel' => $typeLabel,
            'piloteNom' => $pilote ? trim($pilote->getFirstName() . ' ' . $pilote->getLastName()) : '—',
            'aeronefImmat' => $aeronef?->getImmatriculation() ?? '—',
            'dateEvenement' => $event->getDateEvenement()?->format('d/m/Y à H\hi'),
            'compteRendu' => $event->getCompteRenduSuivi() ?? '',
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
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
