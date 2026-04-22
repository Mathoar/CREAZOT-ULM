<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\ClientGetter;

#[Route('/admin/stats')]
#[IsGranted('ROLE_USER')]
class StatsController extends AbstractController
{
    public function __construct(
        private readonly Connection $conn,
        private readonly ClientGetter $clientGetter,
    ) {}

    #[Route('/commercial', methods: ['GET'])]
    public function commercial(Request $request): JsonResponse
    {
        $clientId = $this->clientGetter->get()->getId();
        $from = $request->query->get('from', date('Y-01-01'));
        $to = $request->query->get('to', date('Y-m-d'));
        $granularity = $request->query->get('granularity', 'month');

        $truncExpr = match ($granularity) {
            'day'       => "TO_CHAR(p.date, 'YYYY-MM-DD')",
            'week'      => "TO_CHAR(DATE_TRUNC('week', p.date), 'YYYY-\"W\"IW')",
            'month'     => "TO_CHAR(p.date, 'YYYY-MM')",
            'quarter'   => "TO_CHAR(p.date, 'YYYY-\"Q\"') || EXTRACT(QUARTER FROM p.date)",
            'semester'  => "EXTRACT(YEAR FROM p.date) || '-S' || CASE WHEN EXTRACT(MONTH FROM p.date) <= 6 THEN '1' ELSE '2' END",
            'year'      => "TO_CHAR(p.date, 'YYYY')",
            default     => "TO_CHAR(p.date, 'YYYY-MM')",
        };

        $resTruncExpr = str_replace('p.date', 'r.debut', $truncExpr);

        return new JsonResponse([
            'revenue'               => $this->getRevenue($clientId, $from, $to, $truncExpr),
            'payment_modes'         => $this->getPaymentModes($clientId, $from, $to),
            'circuit_types'         => $this->getCircuitTypes($clientId, $from, $to),
            'top_circuits'          => $this->getTopCircuits($clientId, $from, $to),
            'reservation_statuses'  => $this->getReservationStatuses($clientId, $from, $to, $resTruncExpr),
            'ticket_moyen'          => $this->getTicketMoyen($clientId, $from, $to),
            'prepayment_conversion' => $this->getPrepaymentConversion($clientId, $from, $to),
            'origines'              => $this->getOrigines($clientId, $from, $to),
        ]);
    }

    private function getRevenue(int $clientId, string $from, string $to, string $truncExpr): array
    {
        $total = (float) $this->conn->fetchOne(
            "SELECT COALESCE(SUM(v.prix), 0) FROM vol v
             JOIN prestation p ON v.prestation_id = p.id
             WHERE p.client_id = :cid AND p.date >= :from AND p.date <= :to",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );

        $timeline = $this->conn->fetchAllAssociative(
            "SELECT $truncExpr AS period, COALESCE(SUM(v.prix), 0) AS value, COUNT(v.id) AS count
             FROM vol v JOIN prestation p ON v.prestation_id = p.id
             WHERE p.client_id = :cid AND p.date >= :from AND p.date <= :to
             GROUP BY period ORDER BY period",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );

        return ['total' => $total, 'timeline' => $timeline];
    }

    private function getPaymentModes(int $clientId, string $from, string $to): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT pd.mode, COALESCE(SUM(pd.amount), 0) AS total, COUNT(pd.id) AS count
             FROM payment_detail pd
             JOIN payment pay ON pd.payment_id = pay.id
             WHERE pay.client_id = :cid AND pay.date >= :from AND pay.date <= :to
             GROUP BY pd.mode ORDER BY total DESC",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );
    }

    private function getCircuitTypes(int $clientId, string $from, string $to): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT n.label AS nature, COUNT(v.id) AS count, COALESCE(SUM(v.prix), 0) AS revenue
             FROM vol v
             JOIN prestation p ON v.prestation_id = p.id
             JOIN circuit c ON v.circuit_id = c.id
             LEFT JOIN nature n ON c.nature_id = n.id
             WHERE p.client_id = :cid AND p.date >= :from AND p.date <= :to
             GROUP BY n.label ORDER BY revenue DESC",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );
    }

    private function getTopCircuits(int $clientId, string $from, string $to): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT c.code, c.nom, COUNT(v.id) AS count, COALESCE(SUM(v.prix), 0) AS revenue
             FROM vol v
             JOIN prestation p ON v.prestation_id = p.id
             JOIN circuit c ON v.circuit_id = c.id
             WHERE p.client_id = :cid AND p.date >= :from AND p.date <= :to
             GROUP BY c.id, c.code, c.nom ORDER BY revenue DESC LIMIT 10",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );
    }

    private function getReservationStatuses(int $clientId, string $from, string $to, string $truncExpr): array
    {
        $byStatus = $this->conn->fetchAllAssociative(
            "SELECT r.statut, COUNT(r.id) AS count
             FROM reservation r
             WHERE r.client_id = :cid AND r.debut >= :from AND r.debut <= :to
             GROUP BY r.statut ORDER BY count DESC",
            ['cid' => $clientId, 'from' => $from, 'to' => $to . ' 23:59:59']
        );

        $timeline = $this->conn->fetchAllAssociative(
            "SELECT $truncExpr AS period, r.statut, COUNT(r.id) AS count
             FROM reservation r
             WHERE r.client_id = :cid AND r.debut >= :from AND r.debut <= :to
             GROUP BY period, r.statut ORDER BY period",
            ['cid' => $clientId, 'from' => $from, 'to' => $to . ' 23:59:59']
        );

        return ['by_status' => $byStatus, 'timeline' => $timeline];
    }

    private function getTicketMoyen(int $clientId, string $from, string $to): float
    {
        $result = $this->conn->fetchAssociative(
            "SELECT COALESCE(SUM(v.prix), 0) AS total, COUNT(v.id) AS count
             FROM vol v JOIN prestation p ON v.prestation_id = p.id
             WHERE p.client_id = :cid AND p.date >= :from AND p.date <= :to",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );

        return $result['count'] > 0 ? round((float) $result['total'] / (int) $result['count'], 2) : 0;
    }

    private function getPrepaymentConversion(int $clientId, string $from, string $to): array
    {
        $result = $this->conn->fetchAssociative(
            "SELECT
                COUNT(c.id) AS total,
                COUNT(CASE WHEN r.id IS NOT NULL THEN 1 END) AS used
             FROM cadeau c
             LEFT JOIN reservation r ON r.cadeau_id = c.id
             WHERE c.client_id = :cid AND c.date >= :from AND c.date <= :to",
            ['cid' => $clientId, 'from' => $from, 'to' => $to . ' 23:59:59']
        );

        $total = (int) $result['total'];
        $used = (int) $result['used'];

        return [
            'total' => $total,
            'used'  => $used,
            'rate'  => $total > 0 ? round($used / $total, 2) : 0,
        ];
    }

    // ── Opérationnel ──────────────────────────────────────────────

    #[Route('/operational', methods: ['GET'])]
    public function operational(Request $request): JsonResponse
    {
        $clientId = $this->clientGetter->get()->getId();
        $from = $request->query->get('from', date('Y-01-01'));
        $to = $request->query->get('to', date('Y-m-d'));
        $granularity = $request->query->get('granularity', 'month');

        $truncExpr = match ($granularity) {
            'day'       => "TO_CHAR(p.date, 'YYYY-MM-DD')",
            'week'      => "TO_CHAR(DATE_TRUNC('week', p.date), 'YYYY-\"W\"IW')",
            'month'     => "TO_CHAR(p.date, 'YYYY-MM')",
            'quarter'   => "TO_CHAR(p.date, 'YYYY-\"Q\"') || EXTRACT(QUARTER FROM p.date)",
            'semester'  => "EXTRACT(YEAR FROM p.date) || '-S' || CASE WHEN EXTRACT(MONTH FROM p.date) <= 6 THEN '1' ELSE '2' END",
            'year'      => "TO_CHAR(p.date, 'YYYY')",
            default     => "TO_CHAR(p.date, 'YYYY-MM')",
        };

        return new JsonResponse([
            'flights_timeline'   => $this->getFlightsTimeline($clientId, $from, $to, $truncExpr),
            'utilization_aircraft'=> $this->getUtilizationAircraft($clientId, $from, $to),
            'utilization_pilot'  => $this->getUtilizationPilot($clientId, $from, $to),
            'revenue_by_aircraft'=> $this->getRevenueByAircraft($clientId, $from, $to),
            'pilot_hours'        => $this->getPilotHours($clientId, $from, $to),
            'reservations_timeline' => $this->getReservationsTimeline($clientId, $from, $to, $truncExpr),
        ]);
    }

    private function getFlightsTimeline(int $clientId, string $from, string $to, string $truncExpr): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT $truncExpr AS period, COUNT(p.id) AS prestations, COALESCE(SUM(p.duree), 0) AS heures
             FROM prestation p
             WHERE p.client_id = :cid AND p.date >= :from AND p.date <= :to
             GROUP BY period ORDER BY period",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );
    }

    private function getUtilizationAircraft(int $clientId, string $from, string $to): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT a.immatriculation, COUNT(p.id) AS prestations, COALESCE(SUM(p.duree), 0) AS heures,
                    COALESCE(SUM(v.prix), 0) AS revenue
             FROM aeronef a
             LEFT JOIN prestation p ON p.aeronef_id = a.id AND p.date >= :from AND p.date <= :to
             LEFT JOIN vol v ON v.prestation_id = p.id
             WHERE a.client_id = :cid
             GROUP BY a.id, a.immatriculation ORDER BY heures DESC",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );
    }

    private function getUtilizationPilot(int $clientId, string $from, string $to): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT u.first_name || ' ' || u.last_name AS pilote, COUNT(p.id) AS prestations,
                    COALESCE(SUM(p.duree), 0) AS heures
             FROM \"user\" u
             JOIN prestation p ON p.pilote_id = u.id
             WHERE p.client_id = :cid AND p.date >= :from AND p.date <= :to
             GROUP BY u.id, u.first_name, u.last_name ORDER BY heures DESC",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );
    }

    private function getRevenueByAircraft(int $clientId, string $from, string $to): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT a.immatriculation, COALESCE(SUM(v.prix), 0) AS revenue, COALESCE(SUM(v.cout), 0) AS cout,
                    COUNT(v.id) AS vols
             FROM aeronef a
             JOIN prestation p ON p.aeronef_id = a.id
             JOIN vol v ON v.prestation_id = p.id
             WHERE p.client_id = :cid AND p.date >= :from AND p.date <= :to
             GROUP BY a.id, a.immatriculation ORDER BY revenue DESC",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );
    }

    private function getPilotHours(int $clientId, string $from, string $to): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT u.first_name || ' ' || u.last_name AS pilote,
                    COALESCE(SUM(v.cout), 0) AS remuneration,
                    COALESCE(SUM(v.prix), 0) AS ca,
                    COALESCE(SUM(v.duree), 0) AS heures,
                    COUNT(v.id) AS vols
             FROM \"user\" u
             JOIN prestation p ON COALESCE(p.encadrant_id, p.pilote_id) = u.id
             JOIN vol v ON v.prestation_id = p.id
             WHERE p.client_id = :cid AND p.date >= :from AND p.date <= :to
             GROUP BY u.id, u.first_name, u.last_name ORDER BY remuneration DESC",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );
    }

    private function getReservationsTimeline(int $clientId, string $from, string $to, string $truncExpr): array
    {
        $truncExpr = str_replace('p.date', 'r.debut', $truncExpr);
        return $this->conn->fetchAllAssociative(
            "SELECT $truncExpr AS period, COUNT(r.id) AS count
             FROM reservation r
             WHERE r.client_id = :cid AND r.debut >= :from AND r.debut <= :to
             GROUP BY period ORDER BY period",
            ['cid' => $clientId, 'from' => $from, 'to' => $to . ' 23:59:59']
        );
    }

    // ── Technique ────────────────────────────────────────────────

    #[Route('/technical', methods: ['GET'])]
    public function technical(Request $request): JsonResponse
    {
        $clientId = $this->clientGetter->get()->getId();
        $from = $request->query->get('from', date('Y-01-01'));
        $to = $request->query->get('to', date('Y-m-d'));
        $granularity = $request->query->get('granularity', 'month');

        $truncExpr = match ($granularity) {
            'day'       => "TO_CHAR(p.date, 'YYYY-MM-DD')",
            'week'      => "TO_CHAR(DATE_TRUNC('week', p.date), 'YYYY-\"W\"IW')",
            'month'     => "TO_CHAR(p.date, 'YYYY-MM')",
            'quarter'   => "TO_CHAR(p.date, 'YYYY-\"Q\"') || EXTRACT(QUARTER FROM p.date)",
            'semester'  => "EXTRACT(YEAR FROM p.date) || '-S' || CASE WHEN EXTRACT(MONTH FROM p.date) <= 6 THEN '1' ELSE '2' END",
            'year'      => "TO_CHAR(p.date, 'YYYY')",
            default     => "TO_CHAR(p.date, 'YYYY-MM')",
        };

        return new JsonResponse([
            'fleet_status'       => $this->getFleetStatus($clientId),
            'maintenance_history'=> $this->getMaintenanceHistory($clientId, $from, $to),
            'horametre_evolution'=> $this->getHorametreEvolution($clientId, $from, $to, $truncExpr),
            'maintenance_forecast'=> $this->getMaintenanceForecast($clientId, $from, $to),
        ]);
    }

    private function getFleetStatus(int $clientId): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT a.immatriculation, a.horametre, a.entretien,
                    (a.entretien - a.horametre) AS heures_avant_maintenance,
                    a.seuil_alerte, a.alerte_envoyee,
                    a.changement_moteur,
                    (a.changement_moteur - a.horametre) AS heures_avant_moteur,
                    a.seuil_alerte_changement_moteur, a.alerte_moteur_envoyee,
                    a.is_available
             FROM aeronef a WHERE a.client_id = :cid
             ORDER BY (a.entretien - a.horametre) ASC",
            ['cid' => $clientId]
        );
    }

    private function getMaintenanceHistory(int $clientId, string $from, string $to): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT e.date, e.intervention, e.horametre_intervention, e.horametre_next_intervention,
                    e.changement_moteur, a.immatriculation
             FROM entretien e
             JOIN aeronef a ON e.aeronef_id = a.id
             WHERE e.client_id = :cid AND e.date >= :from AND e.date <= :to
             ORDER BY e.date DESC",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );
    }

    private function getHorametreEvolution(int $clientId, string $from, string $to, string $truncExpr): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT a.immatriculation, $truncExpr AS period, COALESCE(SUM(p.duree), 0) AS heures,
                    MAX(p.horametre_fin) AS horametre_fin
             FROM prestation p
             JOIN aeronef a ON p.aeronef_id = a.id
             WHERE p.client_id = :cid AND p.date >= :from AND p.date <= :to
             GROUP BY a.id, a.immatriculation, period
             ORDER BY a.immatriculation, period",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );
    }

    private function getMaintenanceForecast(int $clientId, string $from, string $to): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT a.immatriculation, a.horametre, a.entretien,
                    COALESCE(AVG(p.duree), 0) AS avg_daily_hours,
                    COUNT(p.id) AS total_prestations,
                    CASE WHEN COALESCE(AVG(p.duree), 0) > 0
                         THEN ROUND((a.entretien - a.horametre) / AVG(p.duree))
                         ELSE NULL END AS jours_estimes
             FROM aeronef a
             LEFT JOIN prestation p ON p.aeronef_id = a.id AND p.date >= :from AND p.date <= :to
             WHERE a.client_id = :cid
             GROUP BY a.id, a.immatriculation, a.horametre, a.entretien
             ORDER BY (a.entretien - a.horametre) ASC",
            ['cid' => $clientId, 'from' => $from, 'to' => $to]
        );
    }

    private function getOrigines(int $clientId, string $from, string $to): array
    {
        return $this->conn->fetchAllAssociative(
            "SELECT o.name, COUNT(DISTINCT r.id) AS count, COALESCE(SUM(r.prix), 0) AS revenue
             FROM origine o
             JOIN reservation_origine ro ON ro.origine_id = o.id
             JOIN reservation r ON ro.reservation_id = r.id
             WHERE r.client_id = :cid AND r.debut >= :from AND r.debut <= :to
             GROUP BY o.id, o.name ORDER BY count DESC",
            ['cid' => $clientId, 'from' => $from, 'to' => $to . ' 23:59:59']
        );
    }
}
