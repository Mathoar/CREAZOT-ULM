<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ConversationThreadRepository;
use App\Service\AvailabilityService;
use App\Service\ConversationManager;
use App\Service\ReservationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VapiWebhookController extends AbstractController
{
    public function __construct(
        private AvailabilityService $availabilityService,
        private ConversationManager $conversationManager,
        private ReservationFactory $reservationFactory,
        private ConversationThreadRepository $threadRepo,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {}

    #[Route('/webhook/vapi/{clientId}', name: 'vapi_webhook', methods: ['POST'], requirements: ['clientId' => '\d+'])]
    public function handleWebhook(int $clientId, Request $request): JsonResponse
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);
        if (!$client) {
            $this->logger->warning('Vapi webhook: client {id} not found', ['id' => $clientId]);
            return new JsonResponse(['error' => 'Client not found'], 404);
        }

        $payload = json_decode($request->getContent(), true);

        if (!$payload) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $type = $payload['message']['type'] ?? null;
        $this->logger->error('[VAPI] webhook type={type} client={client}', [
            'type' => $type ?? 'null',
            'client' => $clientId,
        ]);

        if (!$type) {
            return new JsonResponse(['ok' => true]);
        }

        $response = match ($type) {
            'tool-calls' => $this->handleToolCalls($payload, $client),
            'end-of-call-report' => $this->handleEndOfCall($payload, $client),
            'status-update' => new JsonResponse(['ok' => true]),
            'assistant-request' => new JsonResponse(['ok' => true]),
            default => new JsonResponse(['ok' => true]),
        };

        $this->logger->error('[VAPI] response for {type}: {body}', [
            'type' => $type,
            'body' => $response->getContent(),
        ]);

        return $response;
    }

    private function handleToolCalls(array $payload, Client $client): JsonResponse
    {
        $toolCallList = $payload['message']['toolCallList'] ?? [];
        $results = [];

        $this->logger->error('[VAPI] toolCallList count={count}', [
            'count' => count($toolCallList),
        ]);

        foreach ($toolCallList as $toolCall) {
            $name = $toolCall['function']['name']
                ?? $toolCall['name']
                ?? '';
            $params = $toolCall['function']['arguments']
                ?? $toolCall['parameters']
                ?? $toolCall['arguments']
                ?? [];
            $callId = $toolCall['id'] ?? '';

            if (is_string($params)) {
                $params = json_decode($params, true) ?? [];
            }

            $this->logger->error('[VAPI] tool call: {name} id={callId} params={params}', [
                'name' => $name,
                'callId' => $callId,
                'params' => json_encode($params),
            ]);

            $result = match ($name) {
                'check_availability' => $this->toolCheckAvailability($params, $payload, $client),
                'confirm_reservation' => $this->toolConfirmReservation($params, $payload, $client),
                'list_services' => $this->toolListServices($client),
                'check_gift_code' => $this->toolCheckGiftCode($params, $client),
                default => "Outil non reconnu : {$name}",
            };

            $thread = $this->trackConversation($payload, $client, []);
            if ($thread) {
                $this->conversationManager->appendMessage($thread, 'system', "Tool {$name}: " . (is_string($result) ? $result : json_encode($result)));
            }

            $results[] = [
                'toolCallId' => $callId,
                'result' => is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_UNICODE),
            ];
        }

        return new JsonResponse(['results' => $results]);
    }

    private function toolCheckAvailability(array $params, array $payload, Client $client): string
    {
        $circuitCode = $params['circuit_code'] ?? '';
        $dateStr = $params['date'] ?? '';

        if (!$circuitCode || !$dateStr) {
            return "Paramètres manquants. J'ai besoin du circuit_code et de la date.";
        }

        $circuits = $this->availabilityService->getAvailableCircuits($client);
        $circuit = null;
        $code = strtoupper(trim($circuitCode));
        foreach ($circuits as $c) {
            if (strtoupper($c->getCode() ?? '') === $code || str_contains(strtoupper($c->getNom() ?? ''), $code)) {
                $circuit = $c;
                break;
            }
        }

        if (!$circuit) {
            $available = array_map(fn($c) => "{$c->getCode()} ({$c->getNom()})", $circuits);
            return "Prestation \"{$circuitCode}\" non trouvée. Prestations disponibles : " . implode(', ', $available);
        }

        try {
            $date = new \DateTime($dateStr);
        } catch (\Exception $e) {
            return "Date invalide : \"{$dateStr}\". Format attendu : YYYY-MM-DD.";
        }

        $slots = $this->availabilityService->findAvailableSlots($client, $circuit, $date, 5);

        if (empty($slots)) {
            return "Aucun créneau disponible pour {$circuit->getNom()} le {$date->format('d/m/Y')}. Proposez une autre date au client.";
        }

        $this->trackConversation($payload, $client, [
            'circuit_id' => $circuit->getId(),
            'circuit_code' => $circuit->getCode(),
            'date' => $dateStr,
            'proposed_slots' => array_map(fn($s) => [
                'debut' => $s['debut']->format('Y-m-d H:i'),
                'fin' => $s['fin']->format('Y-m-d H:i'),
                'aeronef' => $s['aeronef']->getImmatriculation(),
                'prix' => $s['prix'],
            ], $slots),
        ]);

        $lines = [];
        foreach ($slots as $i => $slot) {
            $num = $i + 1;
            $lines[] = "Créneau {$num} : {$this->spokenTime($slot['debut'])} à {$this->spokenTime($slot['fin'])} — {$slot['prix']} euros";
        }

        return "Créneaux disponibles pour {$circuit->getNom()} le {$date->format('d/m/Y')} :\n" . implode("\n", $lines)
            . "\n\nDemandez au client quel créneau il préfère.";
    }

    private function toolConfirmReservation(array $params, array $payload, Client $client): string
    {
        $circuitCode = $params['circuit_code'] ?? '';
        $dateStr = $params['date'] ?? '';
        $timeStr = $params['time'] ?? '';
        $customerName = $params['customer_name'] ?? '';
        $quantity = (int) ($params['quantity'] ?? 1);

        if (!$circuitCode || !$dateStr || !$timeStr || !$customerName) {
            return "Informations insuffisantes pour confirmer. J'ai besoin de : circuit_code, date, time, customer_name.";
        }

        $circuits = $this->availabilityService->getAvailableCircuits($client);
        $circuit = null;
        $code = strtoupper(trim($circuitCode));
        foreach ($circuits as $c) {
            if (strtoupper($c->getCode() ?? '') === $code || str_contains(strtoupper($c->getNom() ?? ''), $code)) {
                $circuit = $c;
                break;
            }
        }

        if (!$circuit) {
            return "Prestation \"{$circuitCode}\" non trouvée.";
        }

        $tz = new \DateTimeZone($client->getTimezone() ?? 'Indian/Reunion');
        try {
            $debut = new \DateTime("{$dateStr} {$timeStr}", $tz);
        } catch (\Exception $e) {
            return "Date/heure invalide.";
        }

        $hours = $circuit->getDuree() ? (int) $circuit->getDuree()->format('H') : 0;
        $minutes = $circuit->getDuree() ? (int) $circuit->getDuree()->format('i') : 0;
        $durationMinutes = ($hours - 20) * 60 + $minutes;
        if ($durationMinutes <= 0) {
            $durationMinutes = $hours * 60 + $minutes;
        }
        $fin = (clone $debut)->modify("+{$durationMinutes} minutes");

        $check = $this->availabilityService->isSlotAvailable($client, $circuit, $debut, $fin);
        if ($check === false) {
            return "Ce créneau n'est plus disponible. Veuillez vérifier les disponibilités à nouveau.";
        }

        $customerPhone = $params['customer_phone'] ?? $payload['message']['call']['customer']['number'] ?? null;

        $thread = $this->trackConversation($payload, $client, [
            'circuit_id' => $circuit->getId(),
            'circuit_code' => $circuit->getCode(),
            'date' => $dateStr,
            'extracted' => [
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'circuit_code' => $circuit->getCode(),
                'preferred_date' => $dateStr,
                'preferred_time' => $timeStr,
                'quantity' => $quantity,
            ],
            'proposed_slots' => [[
                'debut' => $debut->format('Y-m-d H:i'),
                'fin' => $fin->format('Y-m-d H:i'),
                'aeronef' => $check['aeronef']->getImmatriculation(),
                'prix' => $circuit->getPrix() ?? 0,
            ]],
        ]);

        if ($thread) {
            $thread->setCustomerName($customerName);
            $thread->setCustomerPhone($customerPhone);
            $this->conversationManager->updateStatus($thread, 'awaiting_club');
        }

        $this->logger->error('[VAPI] reservation request → awaiting_club for {name}, {circuit} le {date} à {time}', [
            'name' => $customerName,
            'circuit' => $circuit->getNom(),
            'date' => $dateStr,
            'time' => $timeStr,
        ]);

        $clubName = $client->getNom() ?? 'le club';
        return "Parfait {$customerName} ! J'ai bien noté votre demande pour un {$circuit->getNom()} le {$debut->format('d/m/Y')} à {$this->spokenTime($debut)}. "
            . "Je transmets votre demande à l'équipe de {$clubName}. "
            . "Un membre de l'équipe vous recontactera très rapidement pour confirmer votre réservation. "
            . "Avez-vous d'autres questions ?";
    }

    private function toolListServices(Client $client): string
    {
        $circuits = $this->availabilityService->getAvailableCircuits($client);
        if (empty($circuits)) {
            return "Aucune prestation configurée pour ce club.";
        }

        $lines = [];
        foreach ($circuits as $c) {
            $nature = $c->getNature()?->getLabel() ?? '';
            if (stripos($nature, 'Local') === false || stripos($nature, 'Onéreux') === false) {
                continue;
            }
            $duree = $this->formatDuration($c->getDuree());
            $prix = $c->getPrix() ? number_format($c->getPrix(), 2, ',', ' ') . ' euros' : 'sur devis';
            $lines[] = "- {$c->getNom()} — Durée : {$duree} — Prix : {$prix}";
        }

        if (empty($lines)) {
            return "Aucune prestation disponible pour ce club.";
        }

        return "Voici nos prestations :\n" . implode("\n", $lines);
    }

    private function formatDuration(?\DateTimeInterface $duree): string
    {
        if (!$duree) {
            return 'non définie';
        }

        $hours = (int) $duree->format('H');
        $minutes = (int) $duree->format('i');
        $totalMinutes = ($hours - 20) * 60 + $minutes;
        if ($totalMinutes <= 0) {
            $totalMinutes = $hours * 60 + $minutes;
        }

        if ($totalMinutes >= 60) {
            $h = intdiv($totalMinutes, 60);
            $m = $totalMinutes % 60;
            return $m > 0 ? sprintf('%dh%02d', $h, $m) : "{$h}h";
        }

        return "{$totalMinutes} minutes";
    }

    private function toolCheckGiftCode(array $params, Client $client): string
    {
        $code = $params['gift_code'] ?? '';
        if (!$code) {
            return "Aucun code cadeau fourni.";
        }

        $cadeau = $this->em->getRepository(\App\Entity\Cadeau::class)->findOneBy([
            'code' => $code,
            'client' => $client,
            'used' => false,
        ]);

        if (!$cadeau) {
            return "Ce code cadeau n'est pas valide ou a déjà été utilisé.";
        }

        $circuit = $cadeau->getCircuit();
        $circuitInfo = $circuit ? "{$circuit->getCode()} ({$circuit->getNom()}) — valeur : {$cadeau->getMontant()} €" : "Non lié à une prestation spécifique";

        return "Bon cadeau valide ! {$circuitInfo}. Le client peut réserver avec ce bon.";
    }

    private function handleEndOfCall(array $payload, Client $client): JsonResponse
    {
        $call = $payload['message']['call'] ?? [];
        $callId = $call['id'] ?? 'unknown';
        $duration = $call['duration'] ?? 0;
        $summary = $payload['message']['summary'] ?? null;

        $this->logger->info('Vapi: call ended #{callId}, duration: {duration}s', [
            'callId' => $callId,
            'duration' => $duration,
        ]);

        $thread = $this->threadRepo->findOneBy(['externalConversationId' => $callId]);
        if ($thread && $summary) {
            $this->conversationManager->updateSummary($thread, $summary);
        }

        return new JsonResponse(['ok' => true]);
    }

    private function trackConversation(array $payload, Client $client, array $contextUpdate): ?\App\Entity\ConversationThread
    {
        $call = $payload['message']['call'] ?? [];
        $callId = $call['id'] ?? null;
        $customerPhone = $call['customer']['number'] ?? null;

        if (!$callId) {
            return null;
        }

        $thread = $this->threadRepo->findOneBy(['externalConversationId' => $callId]);

        if (!$thread) {
            $thread = $this->conversationManager->createThread('voice', $client, null, null, $customerPhone);
            $thread->setExternalConversationId($callId);
            $this->em->flush();
        }

        $this->conversationManager->updateContext($thread, $contextUpdate);

        return $thread;
    }

    private function spokenTime(\DateTimeInterface $dt): string
    {
        $h = (int) $dt->format('G');
        $m = (int) $dt->format('i');
        if ($m === 0) {
            return "{$h} heures";
        }
        return "{$h} heures {$m}";
    }
}
