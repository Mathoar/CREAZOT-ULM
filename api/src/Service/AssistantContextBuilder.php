<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Entity\Circuit;
use App\Entity\Option;
use App\Entity\Combinaison;
use App\Entity\Origine;
use App\Entity\Cadeau;
use App\Entity\Aeronef;
use Doctrine\ORM\EntityManagerInterface;

class AssistantContextBuilder
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function buildContext(Client $client): array
    {
        $clientId = $client->getId();

        $circuits = $this->em->createQueryBuilder()
            ->select('c')
            ->from(Circuit::class, 'c')
            ->andWhere('c.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getResult();

        $options = $this->em->createQueryBuilder()
            ->select('o')
            ->from(Option::class, 'o')
            ->andWhere('o.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getResult();

        $combinaisons = $this->em->createQueryBuilder()
            ->select('cb')
            ->from(Combinaison::class, 'cb')
            ->andWhere('cb.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getResult();

        $origines = $this->em->createQueryBuilder()
            ->select('or_')
            ->from(Origine::class, 'or_')
            ->andWhere('or_.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getResult();

        $cadeauxActifs = $this->em->createQueryBuilder()
            ->select('ca')
            ->from(Cadeau::class, 'ca')
            ->andWhere('ca.client = :clientId')
            ->andWhere('ca.used = false OR ca.used IS NULL')
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getResult();

        $aeronefsDisponibles = $this->em->createQueryBuilder()
            ->select('a')
            ->from(Aeronef::class, 'a')
            ->andWhere('a.client = :clientId')
            ->andWhere('a.isAvailable = true')
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getResult();

        return [
            'club' => [
                'name' => $client->getName(),
                'phone' => $client->getPhone(),
                'email' => $client->getEmail(),
                'address' => $client->getAddress(),
                'city' => $client->getCity(),
                'zipcode' => $client->getZipcode(),
                'website' => $client->getWebsite(),
            ],
            'horaires' => [
                'min' => $client->getMinHours()?->format('H:i'),
                'max' => $client->getMaxHours()?->format('H:i'),
                'timezone' => $client->getTimezone(),
            ],
            'circuits' => array_map(fn(Circuit $c) => [
                'code' => $c->getCode(),
                'nom' => $c->getNom(),
                'prix' => $c->getPrix(),
                'duree' => $c->getDuree()?->format('H:i'),
                'nature' => $c->getNature()?->getLabel(),
                'qualifications' => array_map(
                    fn($q) => $q->getNom(),
                    $c->getQualifications()->toArray()
                ),
                'needsEncadrant' => $c->getNature()?->getNeedsEncadrant() ?? false,
            ], $circuits),
            'options' => array_map(fn(Option $o) => [
                'nom' => $o->getNom(),
                'prix' => $o->getPrix(),
            ], $options),
            'combinaisons' => array_map(fn(Combinaison $cb) => [
                'nom' => $cb->getNom(),
                'prix' => $cb->getPrix(),
                'options' => $cb->getOptions(),
            ], $combinaisons),
            'origines' => array_map(fn(Origine $or) => [
                'nom' => $or->getName(),
                'remise' => $or->getDiscount(),
            ], $origines),
            'cadeaux_actifs' => array_map(fn(Cadeau $ca) => [
                'code' => $ca->getCode(),
                'circuit' => $ca->getCircuit()?->getNom(),
                'montant' => $ca->getCout(),
            ], $cadeauxActifs),
            'aeronefs' => array_map(fn(Aeronef $a) => [
                'immatriculation' => $a->getImmatriculation(),
            ], $aeronefsDisponibles),
            'email_config' => [
                'hasEmailConfirmation' => $client->getHasEmailConfirmation(),
                'confirmationMessage' => $client->getConfirmationMessage(),
                'emailAddressSender' => $client->getEmailAddressSender(),
            ],
            'modules' => [
                'hasOptions' => $client->getHasOptions(),
                'hasGifts' => $client->getHasGifts(),
            ],
            'custom_instructions' => $client->getAssistantCustomInstructions(),
        ];
    }

    public function buildPrompt(array $context, string $channel): string
    {
        $clubName = $context['club']['name'] ?? 'notre club';
        $sections = [];

        $sections[] = $this->buildIdentitySection($clubName, $channel);
        $sections[] = $this->buildCatalogSection($context['circuits']);
        $sections[] = $this->buildScheduleSection($context['horaires']);

        if (($context['modules']['hasOptions'] ?? false) && !empty($context['options'])) {
            $sections[] = $this->buildOptionsSection($context['options']);
        }

        if (($context['modules']['hasGifts'] ?? false) && !empty($context['cadeaux_actifs'])) {
            $sections[] = $this->buildGiftSection();
        }

        $sections[] = $this->buildRulesSection($clubName, $context['club']);
        $sections[] = $this->buildCustomInstructionsSection($context['custom_instructions'] ?? null);

        if ($channel === 'email') {
            $sections[] = $this->buildEmailFormatSection();
        }

        return implode("\n\n", array_filter($sections));
    }

    private function buildIdentitySection(string $clubName, string $channel): string
    {
        $base = "=== IDENTITÉ ===\nTu es l'assistant de réservation de {$clubName}, un club de vol ULM / aviation légère.";

        if ($channel === 'voice') {
            $base .= "\nTu parles au téléphone. Sois chaleureux, concis, naturel. Ne lis pas de codes internes.";
        } else {
            $base .= "\nTu réponds par email. Sois professionnel, structuré. Tu peux inclure des détails.";
        }

        return $base;
    }

    private function buildCatalogSection(array $circuits): string
    {
        $lines = ["=== CATALOGUE ==="];

        if (empty($circuits)) {
            $lines[] = "Aucune prestation configurée.";
            return implode("\n", $lines);
        }

        foreach ($circuits as $c) {
            $duree = $c['duree'] ?? '?';
            $prix = $c['prix'] !== null ? number_format($c['prix'], 2, ',', ' ') . ' €' : 'sur devis';
            $nature = $c['nature'] ?? '';
            $line = "- [{$c['code']}] {$c['nom']} — Durée : {$duree} — Prix : {$prix}";
            if ($nature) {
                $line .= " — Nature : {$nature}";
            }
            $lines[] = $line;
        }

        $lines[] = "";
        $lines[] = "Table de correspondance nom ↔ code :";
        foreach ($circuits as $c) {
            $lines[] = "  \"{$c['nom']}\" = {$c['code']}";
        }

        return implode("\n", $lines);
    }

    private function buildScheduleSection(array $horaires): string
    {
        $min = $horaires['min'] ?? '?';
        $max = $horaires['max'] ?? '?';
        $tz = $horaires['timezone'] ?? 'Europe/Paris';

        return "=== HORAIRES ===\nLe club est ouvert de {$min} à {$max} (fuseau {$tz}).";
    }

    private function buildOptionsSection(array $options): string
    {
        $lines = ["=== OPTIONS DISPONIBLES ==="];
        foreach ($options as $o) {
            $prix = $o['prix'] !== null ? number_format($o['prix'], 2, ',', ' ') . ' €' : 'inclus';
            $lines[] = "- {$o['nom']} — {$prix}";
        }
        return implode("\n", $lines);
    }

    private function buildGiftSection(): string
    {
        return "=== BONS CADEAUX ===\nLe client peut avoir un bon cadeau. Demande le code si le client mentionne un bon cadeau ou un code promo.";
    }

    private function buildRulesSection(string $clubName, array $clubInfo): string
    {
        $phone = $clubInfo['phone'] ?? '';
        $email = $clubInfo['email'] ?? '';

        $rules = [
            "=== RÈGLES ===",
            "- Toujours parler en français.",
            "- Collecter : prestation souhaitée, date, heure, nom du client, nombre de personnes.",
            "- Ne jamais mentionner d'IDs ou de codes internes au client.",
            "- Attendre la confirmation explicite du client avant de créer la réservation.",
            "- Si aucun créneau n'est disponible à la date demandée, proposer une autre date.",
            "- Terminer en souhaitant un bon vol.",
        ];

        if ($phone) {
            $rules[] = "- En cas de doute, proposer de contacter le club au {$phone}.";
        }
        if ($email) {
            $rules[] = "- L'email du club est {$email}.";
        }

        return implode("\n", $rules);
    }

    private function buildCustomInstructionsSection(?string $instructions): ?string
    {
        if ($instructions === null || trim($instructions) === '') {
            return null;
        }

        return "=== CONSIGNES DU CLUB ===\n{$instructions}";
    }

    private function buildEmailFormatSection(): string
    {
        return <<<'SECTION'
=== FORMAT DE RÉPONSE ===
Réponds en JSON valide (sans markdown) :
{
  "response_to_customer": "Le texte à envoyer au client",
  "intent": "booking|inquiry|cancel|other",
  "extracted": {
    "circuit_code": "code ou null",
    "preferred_date": "YYYY-MM-DD ou null",
    "preferred_time": "HH:MM ou null",
    "quantity": nombre_ou_null,
    "customer_name": "nom ou null"
  },
  "action": "search_availability|confirm_slot|need_more_info|escalate_to_human",
  "summary": "Résumé en une phrase"
}
SECTION;
    }
}
