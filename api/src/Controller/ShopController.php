<?php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use App\Factory\Prepayment\PrepaymentFactoryResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\DynamicMailerFactory;
use App\Service\ClientGetter;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class ShopController extends AbstractController
{
    private string $webhookSecret;
    private DynamicMailerFactory $mailerFactory;
    private ClientGetter $clientGetter;
    private LoggerInterface $logger;

    public function __construct(string $webhookSecret, DynamicMailerFactory $mailerFactory, ClientGetter $clientGetter, LoggerInterface $logger)
    {
        $this->webhookSecret = trim($webhookSecret);
        $this->mailerFactory = $mailerFactory;
        $this->clientGetter = $clientGetter;
        $this->logger = $logger;
    }

    #[Route('/wix/purchase/{clientId}', methods: ['POST'], defaults: ['clientId' => null])]
    #[Route('/wix/purchase', methods: ['POST'])]
    public function wixPurchase(Request $request, PrepaymentFactoryResolver $factoryResolver, EntityManagerInterface $em, ?int $clientId = null): Response
    {
        $raw = $request->getContent();

        $this->logger->info('Wix webhook payload reçu', [
            'clientId' => $clientId,
            'payload' => json_decode($raw, true),
        ]);

        $payload = json_decode($raw, true);
        if (!$payload) {
            return new Response('JSON invalide', Response::HTTP_BAD_REQUEST);
        }

        $payload = $payload['event'] ?? $payload;

        $client = null;
        if ($clientId) {
            $client = $em->getRepository(Client::class)->find($clientId);
            if (!$client) {
                $this->logger->error('Wix webhook: client introuvable', ['clientId' => $clientId]);
                return new Response('Client introuvable', Response::HTTP_NOT_FOUND);
            }
        }

        $secret = $client?->getWixHmacSecret() ?? $this->webhookSecret;
        $signatureHeader = trim($request->headers->get('X-Wix-Velo-Hmac', ''));
        $calculatedHmac = base64_encode(hash_hmac('sha256', $raw, $secret, true));

        if (!hash_equals($calculatedHmac, $signatureHeader)) {
            $this->logger->error('Wix webhook HMAC invalide', [
                'clientId' => $clientId,
                'signatureHeader' => $signatureHeader,
                'calculatedHmac' => $calculatedHmac,
            ]);
            return new Response('Signature invalide', Response::HTTP_FORBIDDEN);
        }

        $factory = $factoryResolver->resolve('wix');
        $prepayments = $factory->createPrepaymentFromPayload($payload, $client);

        try {
            $em->wrapInTransaction(function (EntityManagerInterface $em) use ($prepayments) {
                foreach ($prepayments as $prepayment) {
                    $em->persist($prepayment);
                }
                $em->flush();
            });
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'insertion des prépaiements Wix', [
                'clientId' => $clientId,
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);
            throw $e;
        }

        $this->logger->info('Wix webhook traité avec succès', [
            'clientId' => $clientId,
            'nbPrepayments' => count($prepayments),
        ]);

        return $this->json($prepayments, 200, [], ['groups' => 'Cadeau:read']);
    }
}
