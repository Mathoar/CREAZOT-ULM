<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\KimiAiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class KimiProxyController extends AbstractController
{
    public function __construct(
        private KimiAiService $kimiService,
    ) {}

    #[Route('/admin/ai/notam', name: 'ai_notam_analyze', methods: ['POST'])]
    public function analyzeNotam(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $raw = trim($payload['raw'] ?? '');
        $icao = strtoupper(trim($payload['icao'] ?? ''));

        if (!$raw) {
            return $this->json(['error' => 'Le NOTAM brut est requis.'], 422);
        }

        try {
            $result = $this->kimiService->analyzeNotam($raw, $icao);
            return $this->json(['analysis' => $result]);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    #[Route('/admin/ai/meteo-brief', name: 'ai_meteo_brief', methods: ['POST'])]
    public function briefMeteo(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $metar = trim($payload['metar'] ?? '');
        $taf = trim($payload['taf'] ?? '');
        $icao = strtoupper(trim($payload['icao'] ?? ''));

        if (!$metar && !$taf) {
            return $this->json(['error' => 'Au moins un METAR ou TAF est requis.'], 422);
        }

        try {
            $result = $this->kimiService->briefMeteo($metar, $taf, $icao);
            return $this->json(['briefing' => $result]);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    #[Route('/admin/ai/chat', name: 'ai_chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $message = trim($payload['message'] ?? '');

        if (!$message) {
            return $this->json(['error' => 'Le message est requis.'], 422);
        }

        $systemPrompt = <<<PROMPT
Tu es l'assistant IA de la plateforme CREAZOT-ULM, une application de gestion pour clubs ULM et aéroclubs.
Tu aides les utilisateurs avec :
- Les questions aéronautiques (météo, NOTAMs, réglementation ULM, espaces aériens)
- L'utilisation de la plateforme
- Les bonnes pratiques de vol

Réponds UNIQUEMENT en français. Sois concis, précis et professionnel.
PROMPT;

        try {
            $result = $this->kimiService->chat($systemPrompt, $message);
            return $this->json(['response' => $result]);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    #[Route('/admin/ai/translate', name: 'ai_translate', methods: ['POST'])]
    public function translate(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $text = trim($payload['text'] ?? '');
        $targetLang = strtolower(trim($payload['targetLang'] ?? ''));
        $sourceLang = strtolower(trim($payload['sourceLang'] ?? 'fr'));

        if (!$text) {
            return $this->json(['error' => 'Le texte à traduire est requis.'], 422);
        }
        if (!$targetLang) {
            return $this->json(['error' => 'La langue cible est requise.'], 422);
        }

        $supportedLangs = ['fr', 'en', 'es', 'de', 'it'];
        if (!in_array($targetLang, $supportedLangs, true)) {
            return $this->json(['error' => "Langue cible non supportée. Langues acceptées : " . implode(', ', $supportedLangs)], 422);
        }

        if ($sourceLang === $targetLang) {
            return $this->json(['translation' => $text]);
        }

        try {
            $result = $this->kimiService->translate($text, $targetLang, $sourceLang);
            return $this->json(['translation' => $result]);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    #[Route('/admin/ai/analyze-image', name: 'ai_analyze_image', methods: ['POST'])]
    public function analyzeImage(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $base64 = $payload['image'] ?? '';
        $mimeType = $payload['mimeType'] ?? 'image/png';
        $question = trim($payload['question'] ?? 'Analyse cette carte météo pour un vol ULM.');

        if (!$base64) {
            return $this->json(['error' => "L'image est requise (base64)."], 422);
        }

        $maxSizeBytes = 4 * 1024 * 1024;
        if (strlen($base64) > $maxSizeBytes * 1.37) {
            return $this->json(['error' => "L'image est trop volumineuse (max 4 Mo)."], 422);
        }

        try {
            $result = $this->kimiService->analyzeImage($base64, $mimeType, $question);
            return $this->json(['analysis' => $result]);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }
}
