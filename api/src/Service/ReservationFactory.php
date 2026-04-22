<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Aeronef;
use App\Entity\Cadeau;
use App\Entity\Circuit;
use App\Entity\Client;
use App\Entity\Option;
use App\Entity\Origine;
use App\Entity\ProfilPilote;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ReservationFactory
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {}

    public function createFromAssistant(
        Client $client,
        Circuit $circuit,
        \DateTimeInterface $debut,
        Aeronef $aeronef,
        ProfilPilote $pilote,
        string $customerName,
        string $channel,
        array $options = [],
    ): Reservation {
        $reservation = new Reservation();

        $reservation->setClient($client);
        $reservation->setNom($customerName);
        $reservation->setCircuit($circuit);
        $reservation->setDebut($debut);
        $reservation->setFin($this->calculateEndTime($circuit, $debut));
        $reservation->setAvion($aeronef);
        $reservation->setPilote($pilote->getPilote());
        $reservation->setStatut('WAITING');
        $reservation->setQuantite($options['quantity'] ?? 1);
        $reservation->setCode($this->generateCode());
        $reservation->setEmail($options['email'] ?? null);
        $reservation->setTelephone($options['phone'] ?? null);
        $reservation->setColor($this->generateColor());
        $reservation->setRemarques($options['remarques'] ?? "Réservation créée via assistant IA ({$channel})");
        $reservation->setPaid(false);

        $prix = $circuit->getPrix() ?? 0;

        if (isset($options['optionId'])) {
            $option = $this->em->getRepository(Option::class)->find($options['optionId']);
            if ($option) {
                $reservation->setOption($option);
                $prix += $option->getPrix() ?? 0;
            } else {
                $this->logger->warning('Option introuvable', ['optionId' => $options['optionId']]);
            }
        }

        $reservation->setPrix($prix);

        if (isset($options['cadeauId'])) {
            $cadeau = $this->em->getRepository(Cadeau::class)->find($options['cadeauId']);
            if ($cadeau) {
                $reservation->setCadeau($cadeau);
                $cadeau->setUsed(true);
            } else {
                $this->logger->warning('Cadeau introuvable', ['cadeauId' => $options['cadeauId']]);
            }
        }

        $origine = $this->getOrCreateOrigine($client, $channel);
        $reservation->addOrigine($origine);

        $this->em->persist($reservation);

        $email = $options['email'] ?? null;
        if ($client->getHasEmailConfirmation() && $email) {
            $this->sendConfirmationEmail($reservation, $client, $email);
        }

        $this->logger->info('Réservation créée via assistant IA', [
            'code' => $reservation->getCode(),
            'channel' => $channel,
            'client' => $client->getId(),
            'circuit' => $circuit->getNom(),
        ]);

        return $reservation;
    }

    public function calculateEndTime(Circuit $circuit, \DateTimeInterface $debut): \DateTime
    {
        $duree = $circuit->getDuree();
        $minutes = (int) $duree->format('H') * 60 + (int) $duree->format('i');

        $fin = \DateTime::createFromInterface($debut);
        $fin->modify("+{$minutes} minutes");

        return $fin;
    }

    private function generateCode(): string
    {
        $timestamp = time();
        $random = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);

        return "RESA-{$timestamp}-{$random}";
    }

    private function generateColor(): string
    {
        $r = random_int(120, 220);
        $g = random_int(120, 220);
        $b = random_int(120, 220);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    private function getOrCreateOrigine(Client $client, string $channel): Origine
    {
        $label = $channel === 'voice' ? 'Assistant IA Vocal' : 'Assistant IA Email';

        $origine = $this->em->getRepository(Origine::class)->findOneBy([
            'nom' => $label,
            'client' => $client,
        ]);

        if (!$origine) {
            $origine = new Origine();
            $origine->setNom($label);
            $origine->setClient($client);
            $this->em->persist($origine);
            $this->logger->info("Origine '{$label}' créée pour le client {$client->getId()}");
        }

        return $origine;
    }

    private function sendConfirmationEmail(Reservation $reservation, Client $client, string $emailAddress): void
    {
        try {
            $body = $client->getConfirmationMessage() ?? '';
            $clubName = $client->getNom() ?? 'Logic\'Ciel';

            $replacements = [
                '{NOM}' => $reservation->getNom(),
                '{DATE}' => $reservation->getDebut()->format('d/m/Y'),
                '{HEURE}' => $reservation->getDebut()->format('H\hi'),
                '{PRESTATION}' => $reservation->getCircuit()?->getNom() ?? '',
                '{PRIX}' => number_format($reservation->getPrix() ?? 0, 2, ',', ' ') . ' €',
                '{CODE}' => $reservation->getCode(),
            ];
            $body = str_replace(array_keys($replacements), array_values($replacements), $body);

            $subject = $client->getConfirmationSubject() ?? "Confirmation de réservation — {$clubName}";
            $from = $client->getEmailAddressSender();

            if (!$from) {
                $this->logger->warning('Pas d\'adresse expéditeur configurée, email non envoyé', [
                    'clientId' => $client->getId(),
                ]);
                return;
            }

            $email = (new Email())
                ->from($from)
                ->to($emailAddress)
                ->subject($subject)
                ->text($body);

            $this->mailer->send($email);

            $this->logger->info('Email de confirmation envoyé', [
                'to' => $emailAddress,
                'code' => $reservation->getCode(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Échec envoi email de confirmation', [
                'error' => $e->getMessage(),
                'code' => $reservation->getCode(),
            ]);
        }
    }
}
