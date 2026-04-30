<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Prestation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PrestationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/admin/prestation/{id}/correct-horametre', methods: ['POST'])]
    public function correctHorametre(int $id, Request $request): JsonResponse
    {
        $prestation = $this->em->getRepository(Prestation::class)->find($id);
        if (!$prestation) {
            return new JsonResponse(['error' => 'Prestation introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $newHorametreFin = $data['horametreFin'] ?? null;

        if ($newHorametreFin === null || !is_numeric($newHorametreFin)) {
            return new JsonResponse(['error' => 'horametreFin requis et doit être numérique'], 400);
        }

        $newHorametreFin = (float) $newHorametreFin;
        $horametreDepart = $prestation->getHorametreDepart();

        if ($horametreDepart === null) {
            return new JsonResponse(['error' => 'Horamètre de départ non défini sur la prestation'], 400);
        }

        if ($newHorametreFin <= $horametreDepart) {
            return new JsonResponse(['error' => "L'horamètre de fin doit être supérieur à l'horamètre de départ"], 400);
        }

        $aeronef = $prestation->getAeronef();
        $oldDuree = $prestation->getDuree();

        $newDuree = $this->calculateDuration($horametreDepart, $newHorametreFin, $aeronef->isDecimal());

        $prestation->setHorametreFin($newHorametreFin);
        $prestation->setDuree($newDuree);

        $this->updateAeronefHorametre($prestation, $aeronef, $newHorametreFin);
        $this->updatePilotFlightHours($prestation, $oldDuree, $newDuree, $aeronef->isDecimal());

        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'horametreDepart' => $horametreDepart,
            'horametreFin' => $newHorametreFin,
            'duree' => $newDuree,
            'aeronefHorametre' => $aeronef->getHorametre(),
        ]);
    }

    private function calculateDuration(float $depart, float $fin, ?bool $decimal): float
    {
        if ($decimal) {
            return round($fin - $depart, 2);
        }

        $departDecimal = $this->conventionalToDecimal($depart);
        $finDecimal = $this->conventionalToDecimal($fin);
        $diffDecimal = $finDecimal - $departDecimal;

        return $this->decimalToConventional($diffDecimal);
    }

    private function conventionalToDecimal(float $value): float
    {
        $hours = floor($value);
        $minutes = ($value - $hours) * 100;
        return round($hours + ($minutes / 60), 4);
    }

    private function decimalToConventional(float $decimalHours): float
    {
        $hours = floor($decimalHours);
        $minutes = round(($decimalHours - $hours) * 60);
        return round($hours + ($minutes / 100), 2);
    }

    private function updateAeronefHorametre(Prestation $prestation, $aeronef, float $newHorametreFin): void
    {
        $latest = $this->em->getRepository(Prestation::class)->findOneBy(
            ['aeronef' => $aeronef],
            ['date' => 'DESC', 'horametreFin' => 'DESC']
        );

        if ($latest && $latest->getId() === $prestation->getId()) {
            $aeronef->setHorametre($newHorametreFin);
        }
    }

    private function updatePilotFlightHours(Prestation $prestation, ?float $oldDuree, float $newDuree, ?bool $decimal): void
    {
        $pilote = $prestation->getPilote();
        if (!$pilote) return;

        $profil = $pilote->getProfilPilote();
        if (!$profil) return;

        $oldDecimal = $decimal ? ($oldDuree ?? 0) : $this->conventionalToDecimal($oldDuree ?? 0);
        $newDecimal = $decimal ? $newDuree : $this->conventionalToDecimal($newDuree);

        $current = $profil->getTotalFlightHours() ?? 0;
        $profil->setTotalFlightHours(round($current - $oldDecimal + $newDecimal, 2));
    }
}
