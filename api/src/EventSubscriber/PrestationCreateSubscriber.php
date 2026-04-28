<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Aeronef;
use App\Entity\Prestation;
use App\Entity\Client;
use App\Entity\Vol;
use App\Entity\User;
use App\Factory\CarnetVol\CarnetVolFactory;
use App\Service\DynamicMailerFactory;
use App\Service\ClientGetter;
use App\Service\PilotValidityChecker;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;

final class PrestationCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DynamicMailerFactory $dynamicMailerFactory,
        private ClientGetter $clientGetter,
        private Security $security,
        private PilotValidityChecker $pilotValidityChecker,
        private CarnetVolFactory $carnetVolFactory,
        private LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['updateData', EventPriorities::PRE_WRITE],
        ];
    }

    public function updateData(ViewEvent $event): void
    {
        $prestation = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$prestation instanceof Prestation || !in_array($method, [Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_PATCH])) {
            return;
        }

        $user = $this->security->getUser();
        $client = $this->clientGetter->get();
        $this->setEditionMetas($prestation, $user, $method);

        if (Request::METHOD_POST === $method) {
            $aeronef = $prestation->getAeronef();
            $profil = $prestation->getPilote()?->getProfilPilote();
            $aeronef->setHorametre($prestation->getHorametreFin());

            if (\is_null($aeronef->getSeuilAlerte())) {
                $aeronef->setSeuilAlerte(10);
            }

            $this->setFlightsCost($prestation, $aeronef);
            $this->checkDeadlines($aeronef);
            $this->checkParachuteDeadline($aeronef);
            $this->addFlightTimeToUser($prestation->getDuree(), $prestation->getPilote(), $aeronef);
            $this->pilotValidityChecker->checkAndNotify($user);

            if (!\is_null($client) && $client->getHasIndividualFlightLogs()) {
                $carnetsVol = $this->carnetVolFactory->createFromPrestation($prestation, $user);
                foreach ($carnetsVol as $carnetVol) {
                    $profil->addCarnetVol($carnetVol);
                }
            }
        }
    }

    private function setEditionMetas(Prestation $prestation, ?User $user, string $method): void
    {
        if ($method === Request::METHOD_POST) {
            $prestation->setCreatedAt(new \DateTimeImmutable());
            $prestation->setCreatedBy($user);
        } else {
            $prestation->setUpdatedAt(new \DateTimeImmutable());
            $prestation->setUpdatedBy($user);
        }

        foreach ($prestation->getVols() as $vol) {
            if ($method === Request::METHOD_POST) {
                $vol->setCreatedAt(new \DateTimeImmutable());
                $vol->setCreatedBy($user);
            } else {
                $vol->setUpdatedAt(new \DateTimeImmutable());
                $vol->setUpdatedBy($user);
            }
        }
    }

    private function setFlightsCost(Prestation $prestation, Aeronef $aeronef): void
    {
        foreach ($prestation->getVols() as $vol) {
            $defaultCost = $this->calculateFlightCost($prestation, $aeronef, $vol);
            $vol->setCout($defaultCost);

            if ($vol->getTauxTva() === null && $vol->getCircuit()?->getTauxTva() !== null) {
                $vol->setTauxTva($vol->getCircuit()->getTauxTva());
            }
        }
    }

    private function calculateFlightCost(Prestation $prestation, Aeronef $aeronef, Vol $vol): float
    {
        $circuit = $vol->getCircuit();
        if ($circuit->isPrixFixe()) {
            return $circuit->getCout() * $vol->getQuantite();
        }

        if ($aeronef->isDecimal()) {
            return $vol->getDuree() * $circuit->getCout() * $vol->getQuantite();
        }

        $duree = $this->getDecimalTimeFromLocale($vol->getDuree());
        return $duree * $circuit->getCout() * $vol->getQuantite();
    }

    private function checkDeadlines(Aeronef $aeronef): void
    {
        $client = $this->clientGetter->get();
        if (!$client->getEmailServer() || !$client->getEmailAddressSender()) {
            return;
        }

        $mailer = $this->dynamicMailerFactory->getMailerForClient();

        if (($aeronef->getEntretien() - $aeronef->getHorametre()) < $aeronef->getSeuilAlerte() && !$aeronef->isAlerteEnvoyee()) {
            $this->notifyDeadline($mailer, $aeronef, $client, 'ENTRETIEN');
        }

        $seuilMoteur = $aeronef->getSeuilAlerteChangementMoteur();
        $changementMoteur = $aeronef->getChangementMoteur();
        if ($seuilMoteur !== null && $changementMoteur !== null
            && ($changementMoteur - $aeronef->getHorametre()) < $seuilMoteur
            && !$aeronef->isAlerteMoteurEnvoyee()
        ) {
            $this->notifyDeadline($mailer, $aeronef, $client, 'MOTEUR');
        }
    }

    private function notifyDeadline(MailerInterface $mailer, Aeronef $aeronef, Client $client, string $type): void
    {
        $subject = $type === 'ENTRETIEN' ? 'Entretien proche sur ' : 'Changement moteur proche sur ';
        $template = $type === 'ENTRETIEN' ? 'maintenance.html.twig' : 'changement_moteur.html.twig';
        $entretien = $type === 'ENTRETIEN' ? $aeronef->getEntretien() : $aeronef->getChangementMoteur();
        $introduction = $type === 'ENTRETIEN'
            ? ($aeronef->getHorametre() > $aeronef->getEntretien() ? 'dépassée de' : 'programmée dans')
            : ($aeronef->getHorametre() > $aeronef->getChangementMoteur() ? 'dépassé de' : 'programmé dans');

        try {
            $message = (new TemplatedEmail())
                ->from($client->getEmailAddressSender())
                ->to($client->getEmail())
                ->subject($subject . $aeronef->getImmatriculation())
                ->htmlTemplate('emails/' . $template)
                ->context([
                    'immatriculation' => $aeronef->getImmatriculation(),
                    'horametre' => $aeronef->getHorametre(),
                    'entretien' => $entretien,
                    'time' => $this->getRemainingTime($aeronef, $type),
                    'introduction' => $introduction,
                    'client' => $client,
                ]);

            $this->dynamicMailerFactory->renderAndSend($mailer, $message);

            if ($type === 'ENTRETIEN') {
                $aeronef->setAlerteEnvoyee(true);
            } else {
                $aeronef->setAlerteMoteurEnvoyee(true);
            }

            $this->logger->info('Email alerte {type} envoyé pour {immat}', [
                'type' => $type,
                'immat' => $aeronef->getImmatriculation(),
                'to' => $client->getEmail(),
                'clientId' => $client->getId(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Échec envoi email alerte {type} pour {immat}: {error}', [
                'type' => $type,
                'immat' => $aeronef->getImmatriculation(),
                'error' => $e->getMessage(),
                'clientId' => $client->getId(),
            ]);
        }
    }

    private function checkParachuteDeadline(Aeronef $aeronef): void
    {
        if (!$aeronef->getHasParachute()
            || !$aeronef->getDateReconditionnementParachute()
            || !$aeronef->getPeriodiciteParachuteMois()
            || $aeronef->isAlerteParachuteEnvoyee()
        ) {
            return;
        }

        $client = $this->clientGetter->get();
        if (!$client?->getEmailServer() || !$client->getEmailAddressSender()) {
            return;
        }

        $seuilJours = $client->getSeuilAlerteParachuteJours() ?? 180;
        $dateReconditionnement = $aeronef->getDateReconditionnementParachute();
        $periodicite = $aeronef->getPeriodiciteParachuteMois();

        $echeance = (clone $dateReconditionnement)->modify("+{$periodicite} months");
        $now = new \DateTime();
        $joursRestants = (int) $now->diff($echeance)->format('%r%a');

        if ($joursRestants >= $seuilJours) {
            return;
        }

        $mailer = $this->dynamicMailerFactory->getMailerForClient();
        $depassee = $joursRestants < 0;

        try {
            $message = (new TemplatedEmail())
                ->from($client->getEmailAddressSender())
                ->to($client->getEmail())
                ->subject('Parachute de récupération — ' . $aeronef->getImmatriculation())
                ->htmlTemplate('emails/parachute.html.twig')
                ->context([
                    'immatriculation' => $aeronef->getImmatriculation(),
                    'dateReconditionnement' => $dateReconditionnement->format('d/m/Y'),
                    'echeance' => $echeance->format('d/m/Y'),
                    'joursRestants' => abs($joursRestants),
                    'depassee' => $depassee,
                    'client' => $client,
                ]);

            $this->dynamicMailerFactory->renderAndSend($mailer, $message);
            $aeronef->setAlerteParachuteEnvoyee(true);

            $this->logger->info('Email alerte parachute envoyé pour {immat}', [
                'immat' => $aeronef->getImmatriculation(),
                'to' => $client->getEmail(),
                'clientId' => $client->getId(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Échec envoi email alerte parachute pour {immat}: {error}', [
                'immat' => $aeronef->getImmatriculation(),
                'error' => $e->getMessage(),
                'clientId' => $client->getId(),
            ]);
        }
    }

    private function getRemainingTime(Aeronef $aeronef, string $type): string
    {
        if ($type === 'ENTRETIEN') {
            $decimal = $aeronef->isDecimal() ? $aeronef->getEntretien() : $this->getDecimalTimeFromLocale($aeronef->getEntretien());
        } else {
            $decimal = $aeronef->isDecimal() ? $aeronef->getChangementMoteur() : $this->getDecimalTimeFromLocale($aeronef->getChangementMoteur());
        }

        $decimalHorametre = $aeronef->isDecimal() ? $aeronef->getHorametre() : $this->getDecimalTimeFromLocale($aeronef->getHorametre());

        return $this->getRemains($decimal, $decimalHorametre);
    }

    private function addFlightTimeToUser(float $duration, User $user, Aeronef $aeronef): void
    {
        $profilPilote = $user->getProfilPilote();
        if (!$profilPilote) {
            return;
        }

        $duree = $aeronef->isDecimal() ? $duration : $this->getDecimalTimeFromLocale($duration);
        $currentFlightTime = $profilPilote->getTotalFlightHours() ?? 0;
        $updatedFlightTime = $currentFlightTime + $duree;
        $profilPilote->setTotalFlightHours($updatedFlightTime);
    }

    private function getRemains(float $decimal, float $decimalHorametre): string
    {
        $remainingDecimalTime = $decimal - $decimalHorametre;
        $intRemainingTime = abs((int) $remainingDecimalTime);
        $restRemainingTime = abs($remainingDecimalTime) - $intRemainingTime;
        $minutes = (int) round($restRemainingTime * 60);
        $formattedRest = str_pad((string) $minutes, 2, '0', STR_PAD_LEFT);

        return $intRemainingTime . 'h' . $formattedRest;
    }

    private function getDecimalTimeFromLocale(float $duration): float
    {
        $hours = floor($duration);
        $minutes = ($duration - $hours) * 100;
        $decimal = $hours + ($minutes / 60);
        return round($decimal, 2);
    }
}
