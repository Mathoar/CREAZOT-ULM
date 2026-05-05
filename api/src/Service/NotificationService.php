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

    private const PATTERN_TEXTINGHOUSE = 'textinghouse_sms';
    private const PATTERN_TWILIO = 'twilio_sms';

    /**
     * Envoie un SMS via le bon provider selon le préfixe du numéro :
     *   +262 → TextingHouse (route locale Réunion)
     *   Tout le reste → Twilio (numéro +33 dédié)
     * @return string L'identifiant du message renvoyé par le provider
     */
    public function sendSms(string $to, string $body, Client $client): string
    {
        $formattedTo = $this->formatPhoneNumber($to, $client);
        $sanitizedBody = $this->gsmSanitizer->sanitize($body);
        $patternCode = $this->resolveSmsPattern($formattedTo);

        $context = [
            'to' => $formattedTo,
            'to_raw' => ltrim($formattedTo, '+'),
            'body' => $sanitizedBody,
        ];

        if ($patternCode === self::PATTERN_TEXTINGHOUSE) {
            $context['sender_id_raw'] = $client->isSmsSenderIdApproved() && $client->getSmsSenderId()
                ? substr($client->getSmsSenderId(), 0, 11)
                : '';
            $context['sender_id'] = '';
        } else {
            $settings = $this->getSiteSettings();
            $fromNumber = $settings->getTwilioFromNumber() ?? '';
            $context['sender_id'] = preg_replace('/[^0-9+]/', '', $fromNumber);
            $context['sender_id_raw'] = '';
        }

        $result = $this->engine->execute($patternCode, $client, null, $context);

        $messageId = $result['normalized']['messageId']
            ?? $result['raw']['sid']
            ?? $result['raw']['id']
            ?? '';

        $client->incrementSmsCount();
        $this->em->flush();

        $this->logger->info('SMS envoyé via {pattern}', [
            'to' => $formattedTo,
            'messageId' => $messageId,
            'pattern' => $patternCode,
            'clientId' => $client->getId(),
            'smsCount' => $client->getSmsCount(),
        ]);

        return (string) $messageId;
    }

    /**
     * +262 → TextingHouse (route locale Réunion, fiable)
     * Tout le reste → Twilio (numéro +33, routes directes opérateurs métro)
     */
    private function resolveSmsPattern(string $formattedPhone): string
    {
        if (str_starts_with($formattedPhone, '+262')) {
            return self::PATTERN_TEXTINGHOUSE;
        }

        return self::PATTERN_TWILIO;
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

    private const COUNTRY_DIAL_CODES = [
        'FR' => '33',
        'RE' => '262',
        'GP' => '590',
        'MQ' => '596',
        'GF' => '594',
        'YT' => '262',
        'NC' => '687',
        'PF' => '689',
        'BE' => '32',
        'CH' => '41',
        'LU' => '352',
    ];

    private function formatPhoneNumber(string $phone, ?Client $client = null): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        if (str_starts_with($phone, '0')) {
            $dialCode = $this->resolveDialCodeForLocalNumber($phone, $client);
            return '+' . $dialCode . substr($phone, 1);
        }

        return '+' . $phone;
    }

    /**
     * Résout l'indicatif pour un numéro local (commençant par 0) selon le plan ARCEP :
     *   0692/0693 → +262 (Réunion mobile)
     *   0694      → +262 (Mayotte mobile)
     *   06xx/07xx → +33  (métropole mobile, toujours)
     *   Autres    → indicatif du client ou +33 par défaut
     */
    private function resolveDialCodeForLocalNumber(string $phone, ?Client $client): string
    {
        if (preg_match('/^069[234]/', $phone)) {
            return '262';
        }

        if (preg_match('/^0[67]/', $phone)) {
            return '33';
        }

        if ($client?->getCountryCode()) {
            $isoCode = $client->getCountryCode()->getCode();
            return self::COUNTRY_DIAL_CODES[$isoCode] ?? '33';
        }

        return '33';
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
