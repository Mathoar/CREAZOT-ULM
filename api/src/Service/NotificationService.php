<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Reservation;
use App\Entity\SiteSettings;
use App\Service\Integration\IntegrationEngine;
use App\Service\Sms\GsmSanitizer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    private const SMS_CAPABILITY = 'sms_send';

    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private IntegrationEngine $engine,
        private GsmSanitizer $gsmSanitizer,
    ) {}

    /**
     * Résout les variables d'un template avec les données d'une réservation.
     */
    public function resolveTemplate(string $body, Reservation $reservation, Client $client): string
    {
        $circuit = $reservation->getCircuit();
        $pilote = $reservation->getPilote();
        $debut = $reservation->getDebut();

        $tz = $client->getTimezone();
        if ($debut && $tz) {
            $debut = (clone $debut)->setTimezone(new \DateTimeZone($tz));
        }

        $nbPersonnes = 1;
        $code = $reservation->getCode();
        if ($code) {
            $nbPersonnes = $this->em->getRepository(Reservation::class)
                ->count(['code' => $code]);
        }

        $shortcode = $reservation->getPublicShortcode();
        $lienBriefing = '';
        if ($shortcode && $client->getHasPlanification()) {
            $baseUrl = rtrim($_ENV['APP_PUBLIC_URL'] ?? 'https://logic-ciel.com', '/');
            $lienBriefing = $baseUrl . '/r/' . $shortcode;
        }

        $variables = [
            '{{nom}}' => $reservation->getNom() ?? '',
            '{{circuit}}' => $circuit?->getNom() ?? '',
            '{{date}}' => $debut ? $debut->format('d/m/Y') : '',
            '{{heure}}' => $debut ? $debut->format('H:i') : '',
            '{{pilote}}' => $pilote ? trim($pilote->getFirstName() . ' ' . $pilote->getLastName()) : '',
            '{{code}}' => $code ?? '',
            '{{structure}}' => $client->getName() ?? '',
            '{{telephone}}' => $reservation->getTelephone() ?? '',
            '{{email}}' => $reservation->getEmail() ?? '',
            '{{nb_personnes}}' => (string) $nbPersonnes,
            '{{lien_briefing}}' => $lienBriefing,
        ];

        return str_replace(array_keys($variables), array_values($variables), $body);
    }

    /**
     * Envoie un SMS via le pattern d'intégration associé au client (capability sms_send).
     * Supporte plusieurs providers (Twilio, MessageBird, …) sans modifier ce service.
     * @return string L'identifiant du message renvoyé par le provider (sid Twilio, id MessageBird, …)
     */
    public function sendSms(string $to, string $body, Client $client): string
    {
        $formattedTo = $this->formatPhoneNumber($to);
        $sanitizedBody = $this->gsmSanitizer->sanitize($body);

        $result = $this->engine->executeByCapability(self::SMS_CAPABILITY, $client, null, [
            'to' => $formattedTo,
            'to_raw' => ltrim($formattedTo, '+'),
            'body' => $sanitizedBody,
            'sender_id' => $this->getSenderId($client) ?? '',
            'sender_id_raw' => $client->getSmsSenderId() ? substr($client->getSmsSenderId(), 0, 11) : '',
        ]);

        $messageId = $result['normalized']['messageId']
            ?? $result['raw']['sid']
            ?? $result['raw']['id']
            ?? '';

        $client->incrementSmsCount();
        $this->em->flush();

        $this->logger->info('SMS envoyé via pattern', [
            'to' => $formattedTo,
            'messageId' => $messageId,
            'pattern' => $result['meta']['pattern'] ?? null,
            'clientId' => $client->getId(),
            'smsCount' => $client->getSmsCount(),
        ]);

        return (string) $messageId;
    }

    /**
     * Envoie un email de notification.
     */
    public function sendEmail(string $to, string $subject, string $body, Client $client): void
    {
        $settings = $this->getSiteSettings();
        $senderEmail = $settings->getEmailAddressSender() ?? 'noreply@logic-ciel.com';
        $senderName = $client->getName() ?? 'Logic\'Ciel';

        $email = (new Email())
            ->from(sprintf('%s <%s>', $senderName, $senderEmail))
            ->to($to)
            ->subject($subject)
            ->html(nl2br($body));

        $this->mailer->send($email);

        $this->logger->info('Email notification envoyé', [
            'to' => $to,
            'subject' => $subject,
            'clientId' => $client->getId(),
        ]);
    }

    /**
     * Envoie une notification (SMS ou email) à un groupe de réservations.
     * @return array Résumé de l'envoi
     */
    public function sendToGroup(array $reservations, string $body, string $method, Client $client, string $templateTitle = ''): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'errors' => []];

        $grouped = [];
        foreach ($reservations as $reservation) {
            $code = $reservation->getCode() ?: ('solo-' . $reservation->getId());
            $grouped[$code][] = $reservation;
        }

        foreach ($grouped as $code => $groupReservations) {
            $firstReservation = $groupReservations[0];
            $resolvedBody = $this->resolveTemplate($body, $firstReservation, $client);

            try {
                if ($method === 'sms') {
                    $phone = $firstReservation->getTelephone();
                    if (!$phone) {
                        $results['errors'][] = sprintf('Pas de téléphone pour le groupe %s', $code);
                        $results['failed']++;
                        continue;
                    }
                    $this->sendSms($phone, $resolvedBody, $client);
                } else {
                    $email = $firstReservation->getEmail();
                    if (!$email) {
                        $results['errors'][] = sprintf('Pas d\'email pour le groupe %s', $code);
                        $results['failed']++;
                        continue;
                    }
                    $subject = $templateTitle ?: ($client->getName() . ' — Notification');
                    $this->sendEmail($email, $subject, $resolvedBody, $client);
                }

                foreach ($groupReservations as $r) {
                    $r->setNotificationSent(true);
                }
                $results['sent']++;
            } catch (\Exception $e) {
                $this->logger->error('Erreur envoi notification', [
                    'group' => $code,
                    'method' => $method,
                    'error' => $e->getMessage(),
                ]);
                $results['errors'][] = sprintf('Groupe %s : %s', $code, $e->getMessage());
                $results['failed']++;
            }
        }

        $this->em->flush();

        return $results;
    }

    /**
     * Sender ID : utilise client.smsSenderId (numéro ou texte alpha max 11 chars).
     * Fallback sur client.name nettoyé si non renseigné.
     */
    private function getSenderId(Client $client): ?string
    {
        $senderId = $client->getSmsSenderId();
        if ($senderId) {
            return substr($senderId, 0, 11);
        }

        $name = $client->getName();
        if (!$name) {
            return null;
        }
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', $name);
        return substr($clean, 0, 11) ?: null;
    }

    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (str_starts_with($phone, '0') && strlen($phone) === 10) {
            return '+33' . substr($phone, 1);
        }
        if (str_starts_with($phone, '06') || str_starts_with($phone, '07')) {
            return '+33' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '+')) {
            return '+' . $phone;
        }
        return $phone;
    }

    private function getSiteSettings(): SiteSettings
    {
        $settings = $this->em->getRepository(SiteSettings::class)->findOneBy([]);
        if (!$settings) {
            throw new \RuntimeException('SiteSettings introuvable');
        }
        return $settings;
    }
}
