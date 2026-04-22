<?php

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class PilotValidityChecker
{
    public function __construct(
        private DynamicMailerFactory $dynamicMailerFactory,
        private ClientGetter $clientGetter,
        private LoggerInterface $logger,
    ) {}

    public function checkAndNotify(User $user): void
    {
        $profil = $user->getProfilPilote();
        if (!$profil) {
            return;
        }

        $client = $this->clientGetter->get();
        if (!$client->getEmailServer() || !$client->getEmailAddressSender()) {
            return;
        }

        $alerts = [];
        $medicalThreshold = $client->getSeuilMedical() ?? 30;
        $qualificationThreshold = $client->getSeuilQualifications() ?? 30;

        $certificatMedical = $profil->getCertificatMedical();
        if ($certificatMedical && !$certificatMedical->getIsAlertSent() && $this->isValidityBelowThreshold($certificatMedical->getValidUntil() ?? null, $medicalThreshold)) {
            $validUntil = $certificatMedical->getValidUntil();
            if ($validUntil) {
                $alerts[] = $this->buildAlert('certificat médical', $validUntil);
                $certificatMedical->setIsAlertSent(true);
            }
        }

        foreach ($profil->getPilotQualifications() as $pilotqualification) {
            $validUntil = $pilotqualification->getValidUntil();
            if (!$pilotqualification->getIsAlertSent() && $this->isValidityBelowThreshold($validUntil ?? null, $qualificationThreshold)) {
                if ($validUntil) {
                    $qualification = $pilotqualification->getQualification();
                    $alerts[] = $this->buildAlert("qualification '" . $qualification->getNom() . "'", $validUntil);
                    $pilotqualification->setIsAlertSent(true);
                }
            }
        }

        if (empty($alerts)) {
            return;
        }

        try {
            $mailer = $this->dynamicMailerFactory->getMailerForClient();

            $message = (new TemplatedEmail())
                ->from($client->getEmailAddressSender())
                ->to($user->getEmail())
                ->subject('Alerte validité — ' . $client->getName())
                ->htmlTemplate('emails/pilot_validity.html.twig')
                ->context([
                    'user' => $user,
                    'alerts' => $alerts,
                    'client' => $client,
                ]);

            $this->dynamicMailerFactory->renderAndSend($mailer, $message);

            $this->logger->info('Email alerte validité pilote envoyé', [
                'to' => $user->getEmail(),
                'alertCount' => count($alerts),
                'clientId' => $client->getId(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Échec envoi email alerte validité pilote: {error}', [
                'error' => $e->getMessage(),
                'to' => $user->getEmail(),
                'clientId' => $client->getId(),
            ]);
        }
    }

    private function isValidityBelowThreshold(?\DateTimeInterface $validUntil, int $threshold): bool
    {
        if ($validUntil === null) {
            return false;
        }

        $today = new \DateTimeImmutable('today');
        if ($validUntil < $today) {
            return true;
        }

        $diff = $today->diff($validUntil);
        return $diff->days <= $threshold;
    }

    /**
     * @return array{label: string, date: string, days: int, expired: bool}
     */
    private function buildAlert(string $label, \DateTimeInterface $validUntil): array
    {
        $today = new \DateTimeImmutable('today');
        $diff = $today->diff($validUntil);

        return [
            'label' => $label,
            'date' => $validUntil->format('d/m/Y'),
            'days' => $diff->days,
            'expired' => $validUntil < $today,
        ];
    }
}
