<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MediaObject;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Endpoint public pour la page passager /r/{shortcode}.
 * - Aucun X-Client-Id requis : la résolution du tenant se fait via le shortcode (jamais énuméré).
 * - Rate-limit IP-based pour empêcher le scraping.
 * - Lien expire automatiquement 7 jours après la date du vol.
 */
#[AsController]
final class PublicReservationController
{
    private const EXPIRATION_DAYS = 7;

    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private RateLimiterFactory $publicReservationLimiter,
    ) {}

    #[Route(
        path: '/r/{shortcode}',
        name: 'public_reservation_view',
        methods: ['GET'],
        requirements: ['shortcode' => '[a-zA-Z0-9]{10}']
    )]
    public function view(string $shortcode, Request $request): JsonResponse
    {
        $clientIp = $request->getClientIp() ?? 'unknown';
        $limiter = $this->publicReservationLimiter->create($clientIp);
        $limit = $limiter->consume(1);
        if (!$limit->isAccepted()) {
            return $this->buildResponse(
                ['error' => 'too_many_requests'],
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        $reservation = $this->em->getRepository(Reservation::class)
            ->findOneBy(['publicShortcode' => $shortcode]);

        if (!$reservation) {
            return $this->buildResponse(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        }

        if ($this->isExpired($reservation)) {
            return $this->buildResponse(['error' => 'expired'], Response::HTTP_NOT_FOUND);
        }

        if ($reservation->getStatut() === 'cancelled' || $reservation->getStatut() === 'annule') {
            return $this->buildResponse(['error' => 'cancelled'], Response::HTTP_NOT_FOUND);
        }

        $client = $reservation->getClient();
        if (!$client) {
            return $this->buildResponse(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        }

        $circuit = $reservation->getCircuit();
        $briefing = $client->getBriefing();
        $tz = $client->getTimezone();

        $debut = $reservation->getDebut();
        if ($debut && $tz) {
            $debut = (clone $debut)->setTimezone(new \DateTimeZone($tz));
        }

        $payload = [
            'client' => [
                'name' => $client->getName(),
                'logo' => is_string($client->getLogo()) ? $client->getLogo() : null,
                'phone' => $client->getPhone(),
                'lat' => $client->getLat(),
                'lng' => $client->getLng(),
                'timezone' => $tz,
                'address' => $client->getAddress(),
                'zipcode' => $client->getZipcode(),
                'city' => $client->getCity(),
                'color' => $client->getColor(),
            ],
            'reservation' => [
                'date' => $debut?->format('Y-m-d'),
                'time' => $debut?->format('H:i'),
                'firstName' => $reservation->getNom(),
                'circuit' => $circuit?->getNom(),
            ],
            'briefing' => $briefing ? [
                'html' => $briefing->getHtml(),
                'headerImage' => $this->mediaUrl($briefing->getHeaderImage()),
                'showMap' => $briefing->isShowMap(),
                'extraContacts' => $briefing->getExtraContacts(),
            ] : null,
            'circuitBriefing' => ($circuit && ($circuit->getBriefingHtml() || $circuit->getBriefingImage())) ? [
                'html' => $circuit->getBriefingHtml(),
                'image' => $this->mediaUrl($circuit->getBriefingImage()),
            ] : null,
        ];

        return $this->buildResponse($payload, Response::HTTP_OK);
    }

    private function isExpired(Reservation $reservation): bool
    {
        $debut = $reservation->getDebut();
        if (!$debut) {
            return false;
        }
        $now = new \DateTime();
        $diff = $now->getTimestamp() - $debut->getTimestamp();
        return $diff > self::EXPIRATION_DAYS * 86400;
    }

    private function mediaUrl(?MediaObject $m): ?string
    {
        if ($m === null) {
            return null;
        }
        if (is_string($m->contentUrl) && $m->contentUrl !== '') {
            return $m->contentUrl;
        }
        if (is_string($m->filePath) && $m->filePath !== '') {
            return '/media/' . $m->filePath;
        }
        return null;
    }

    private function buildResponse(array $data, int $status): JsonResponse
    {
        $response = new JsonResponse($data, $status);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        return $response;
    }
}
