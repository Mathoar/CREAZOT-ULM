<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Issues a Mercure subscriber cookie scoped to the current user's accessible clients.
 *
 * The cookie is HttpOnly and signed by Symfony with MERCURE_SUBSCRIBER_JWT_KEY.
 * Caddy's Mercure hub validates the JWT before allowing subscription to private topics.
 */
#[IsGranted('ROLE_USER')]
class MercureAuthController extends AbstractController
{
    public function __construct(
        private readonly Authorization $authorization,
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * Sets the mercureAuthorization cookie on the response and returns 204.
     * The cookie grants subscription to the per-client AI reservation stats topics.
     *
     * Super-admins get access to ALL clients (they can switch via the ClientSelector).
     * Regular admins only get access to their own client(s).
     */
    #[Route('/admin/mercure/auth', name: 'mercure_auth', methods: ['POST', 'GET'])]
    public function auth(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur invalide.'], 401);
        }

        $clients = $this->isGranted('ROLE_SUPER_ADMIN')
            ? $this->em->getRepository(Client::class)->findAll()
            : $user->getClients();

        $subscribeTopics = [];
        foreach ($clients as $client) {
            $clientId = $client->getId();
            if ($clientId !== null) {
                $subscribeTopics[] = sprintf('/admin/ai-reservation/stats/%d', (int) $clientId);
            }
        }

        $this->authorization->setCookie($request, $subscribeTopics);

        return new JsonResponse(['topics' => $subscribeTopics]);
    }
}
