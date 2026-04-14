<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Repository\SiteSettingsRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VapiService
{
    private const API_BASE = 'https://api.vapi.ai';

    public function __construct(
        private HttpClientInterface $httpClient,
        private SiteSettingsRepository $siteSettingsRepo,
        private AssistantContextBuilder $contextBuilder,
        private LoggerInterface $logger,
    ) {}

    private function getApiKey(): string
    {
        $settings = $this->siteSettingsRepo->findInstance();
        $key = $settings?->getVapiApiKey();

        if (!$key) {
            throw new \RuntimeException('Clé API Vapi non configurée. Rendez-vous dans Paramétrage SaaS.');
        }

        return $key;
    }

    public function createAssistant(Client $client, string $serverUrl): array
    {
        $clubName = $client->getName();
        $systemPrompt = $this->buildAssistantPrompt($client);

        $body = [
            'name' => "Assistant réservation — {$clubName}",
            'firstMessage' => "Bonjour ! Bienvenue chez {$clubName}. Je suis votre assistant de réservation. Comment puis-je vous aider aujourd'hui ?",
            'transcriber' => [
                'provider' => 'deepgram',
                'model' => 'nova-3',
                'language' => 'fr',
            ],
            'model' => [
                'provider' => 'openai',
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                ],
                'tools' => $this->buildTools($serverUrl),
            ],
            'voice' => [
                'provider' => '11labs',
                'voiceId' => 'sarah',
            ],
            'serverUrl' => $serverUrl,
            'endCallMessage' => "Merci pour votre appel ! À très bientôt chez {$clubName}. Bon vol !",
        ];

        return $this->request('POST', '/assistant', $body);
    }

    public function updateAssistant(string $assistantId, Client $client, string $serverUrl): array
    {
        $clubName = $client->getName();
        $systemPrompt = $this->buildAssistantPrompt($client);

        $body = [
            'name' => "Assistant réservation — {$clubName}",
            'firstMessage' => "Bonjour ! Bienvenue chez {$clubName}. Je suis votre assistant de réservation. Comment puis-je vous aider aujourd'hui ?",
            'model' => [
                'provider' => 'openai',
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                ],
                'tools' => $this->buildTools($serverUrl),
            ],
            'serverUrl' => $serverUrl,
        ];

        return $this->request('PATCH', "/assistant/{$assistantId}", $body);
    }

    public function deleteAssistant(string $assistantId): array
    {
        return $this->request('DELETE', "/assistant/{$assistantId}");
    }

    public function listAssistants(): array
    {
        return $this->request('GET', '/assistant');
    }

    public function getAssistant(string $assistantId): array
    {
        return $this->request('GET', "/assistant/{$assistantId}");
    }

    public function listPhoneNumbers(): array
    {
        return $this->request('GET', '/phone-number');
    }

    public function listCalls(int $limit = 20): array
    {
        return $this->request('GET', "/call?limit={$limit}");
    }

    private function buildAssistantPrompt(Client $client): string
    {
        $context = $this->contextBuilder->buildContext($client);
        return $this->contextBuilder->buildPrompt($context, 'voice');
    }

    private function buildTools(string $serverUrl): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'check_availability',
                    'description' => 'Vérifie les créneaux disponibles pour une prestation à une date donnée',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'circuit_code' => ['type' => 'string', 'description' => 'Code ou nom de la prestation'],
                            'date' => ['type' => 'string', 'description' => 'Date au format YYYY-MM-DD'],
                        ],
                        'required' => ['circuit_code', 'date'],
                    ],
                ],
                'async' => false,
                'server' => ['url' => $serverUrl],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'confirm_reservation',
                    'description' => "Confirme et crée la réservation pour le client. N'appeler qu'après confirmation explicite du client.",
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'circuit_code' => ['type' => 'string', 'description' => 'Code de la prestation'],
                            'date' => ['type' => 'string', 'description' => 'Date au format YYYY-MM-DD'],
                            'time' => ['type' => 'string', 'description' => 'Heure au format HH:MM'],
                            'customer_name' => ['type' => 'string', 'description' => 'Nom complet du client'],
                            'customer_phone' => ['type' => 'string', 'description' => 'Numéro de téléphone du client'],
                            'quantity' => ['type' => 'integer', 'description' => 'Nombre de personnes'],
                        ],
                        'required' => ['circuit_code', 'date', 'time', 'customer_name'],
                    ],
                ],
                'async' => false,
                'server' => ['url' => $serverUrl],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_services',
                    'description' => 'Liste toutes les prestations disponibles du club avec prix et durées',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass(),
                        'required' => [],
                    ],
                ],
                'async' => false,
                'server' => ['url' => $serverUrl],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'check_gift_code',
                    'description' => "Vérifie un code cadeau fourni par le client. Retourne la prestation associée et le prix si valide.",
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'gift_code' => ['type' => 'string', 'description' => 'Le code du bon cadeau fourni par le client'],
                        ],
                        'required' => ['gift_code'],
                    ],
                ],
                'async' => false,
                'server' => ['url' => $serverUrl],
            ],
        ];
    }

    private function request(string $method, string $endpoint, ?array $body = null): array
    {
        $apiKey = $this->getApiKey();

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        try {
            $response = $this->httpClient->request($method, self::API_BASE . $endpoint, $options);
            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);

            if ($statusCode >= 400) {
                $errorMsg = $data['message'] ?? $data['error'] ?? json_encode($data);
                if (is_array($errorMsg)) {
                    $errorMsg = implode(', ', $errorMsg);
                }
                $this->logger->error('Vapi API {status}: {error}', ['status' => $statusCode, 'error' => $errorMsg]);
                throw new \RuntimeException("Vapi API erreur {$statusCode} : {$errorMsg}");
            }

            return $data;
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error('Vapi API error: {error}', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Erreur Vapi : ' . $e->getMessage());
        }
    }
}
