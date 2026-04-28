<?php

declare(strict_types=1);

namespace App\Service\Manex;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ManexDataCollector
{
    private const PRO_QUALIFICATIONS = ['Pilote professionnel', 'Instructeur'];

    public function __construct(
        private readonly EntityManagerInterface $em,
        #[Autowire('%image.public_dir%')] private readonly string $publicDir,
    ) {}

    public function collect(Client $client): array
    {
        return [
            'client'      => $this->collectClient($client),
            'pilots'      => $this->collectPilots($client),
            'fleet'       => $this->collectFleet($client),
            'circuits'    => $this->collectCircuits($client),
            'flightRules' => $this->collectFlightRules($client),
            'briefing'    => $this->collectBriefing($client),
            'airports'    => $this->collectAirports($client),
            'encodedLogo' => $this->getEncodedLogo($client),
        ];
    }

    private function collectClient(Client $client): array
    {
        return [
            'name'    => $client->getName(),
            'email'   => $client->getEmail(),
            'phone'   => $client->getPhone(),
            'address' => $client->getAddress(),
            'zipcode' => $client->getZipcode(),
            'city'    => $client->getCity(),
            'website' => $client->getWebsite(),
        ];
    }

    private function collectPilots(Client $client): array
    {
        $users = $this->em->createQueryBuilder()
            ->select('u')
            ->from(\App\Entity\User::class, 'u')
            ->innerJoin('u.clients', 'c')
            ->innerJoin('u.profilPilote', 'pp')
            ->innerJoin('pp.pilotQualifications', 'pq')
            ->innerJoin('pq.qualification', 'q')
            ->where('c.id = :clientId')
            ->andWhere('q.nom IN (:proQuals)')
            ->andWhere('pp.availableByDefault = true')
            ->setParameter('clientId', $client->getId())
            ->setParameter('proQuals', self::PRO_QUALIFICATIONS)
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();

        return array_map(function ($u) {
            $profil = $u->getProfilPilote();
            $qualifs = [];
            if ($profil) {
                foreach ($profil->getPilotQualifications() as $pq) {
                    $q = $pq->getQualification();
                    if ($q) {
                        $qualifs[] = $q->getName();
                    }
                }
            }
            return [
                'firstName'      => $u->getFirstName(),
                'lastName'       => $u->getLastName(),
                'email'          => $u->getEmail(),
                'qualifications' => $qualifs,
                'certificatOk'   => $profil?->getAvailableCertificate() ?? false,
            ];
        }, $users);
    }

    private function collectFleet(Client $client): array
    {
        $aeronefs = $this->em->createQueryBuilder()
            ->select('a')
            ->from(\App\Entity\Aeronef::class, 'a')
            ->where('a.client = :client')
            ->andWhere('a.archived = false')
            ->setParameter('client', $client)
            ->orderBy('a.immatriculation', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(function ($a) {
            $echeance = null;
            if ($a->getHasParachute() && $a->getDateReconditionnementParachute() && $a->getPeriodiciteParachuteMois()) {
                $echeance = (clone $a->getDateReconditionnementParachute())
                    ->modify("+{$a->getPeriodiciteParachuteMois()} months")
                    ->format('d/m/Y');
            }

            return [
                'immatriculation'         => $a->getImmatriculation(),
                'immatriculationComplete' => $a->getImmatriculationComplete(),
                'modele'                  => $a->getModele(),
                'isAvailable'             => $a->getIsAvailable(),
                'typeBalise'              => $a->getTypeBalise(),
                'codeBalise'              => $a->getCodeBalise(),
                'hasParachute'            => $a->getHasParachute(),
                'echeanceParachute'       => $echeance,
            ];
        }, $aeronefs);
    }

    private function collectCircuits(Client $client): array
    {
        $circuits = $this->em->createQueryBuilder()
            ->select('c')
            ->from(\App\Entity\Circuit::class, 'c')
            ->where('c.client = :client')
            ->andWhere('c.isAvailable = true')
            ->setParameter('client', $client)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();

        $clientTz = new \DateTimeZone($client->getTimezone() ?? 'Europe/Paris');

        return array_map(function ($c) use ($clientTz) {
            $dureeStr = null;
            $duree = $c->getDuree();
            if ($duree instanceof \DateTimeInterface) {
                $dt = \DateTime::createFromInterface($duree);
                $dt->setTimezone($clientTz);
                $dureeStr = sprintf('%dh%02d', (int) $dt->format('H'), (int) $dt->format('i'));
            }

            $qualifs = [];
            foreach ($c->getQualifications() as $q) {
                $qualifs[] = $q->getName();
            }

            return [
                'nom'                   => $c->getNom(),
                'code'                  => $c->getName(),
                'duree'                 => $dureeStr,
                'nature'                => $c->getNature()?->getLabel(),
                'isParticularActivity'  => $c->getNature()?->getIsParticularActivity() ?? false,
                'qualifications'        => $qualifs,
            ];
        }, $circuits);
    }

    private function collectFlightRules(Client $client): array
    {
        $rules = $this->em->getRepository(\App\Entity\FlightRule::class)
            ->findBy(['client' => $client]);

        return array_map(fn($r) => [
            'name'              => $r->getName(),
            'limiteWindKts'     => $r->getLimiteWindKts(),
            'maxWindKts'        => $r->getMaxWindKts(),
            'limiteGustKts'     => $r->getLimiteGustKts(),
            'maxGustKts'        => $r->getMaxGustKts(),
            'limiteVisibilityM' => $r->getLimiteVisibilityM(),
            'minVisibilityM'    => $r->getMinVisibilityM(),
            'limiteCeilingFt'   => $r->getLimiteCeilingFt(),
            'minCeilingFt'      => $r->getMinCeilingFt(),
        ], $rules);
    }

    private function collectBriefing(Client $client): ?array
    {
        $briefing = $client->getBriefing();
        if (!$briefing) {
            return null;
        }

        return [
            'html'          => $briefing->getHtml(),
            'extraContacts' => $briefing->getExtraContacts(),
        ];
    }

    private function collectAirports(Client $client): array
    {
        $airports = $client->getAirports();
        $result = [];
        foreach ($airports as $airport) {
            $result[] = [
                'nom'  => $airport->getNom(),
                'code' => $airport->getCode(),
                'main' => $airport->isMain(),
            ];
        }
        return $result;
    }

    private function getEncodedLogo(Client $client): ?string
    {
        $logo = $client->getLogo();
        if (!$logo || !is_string($logo)) {
            return null;
        }

        $path = rtrim($this->publicDir, '/') . $logo;
        if (!file_exists($path)) {
            return null;
        }

        $mime = str_ends_with(strtolower($path), '.png') ? 'image/png' : 'image/jpeg';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}
