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
                'needsEncadrant' => $c->isNeedsEncadrant(),
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
        $sections[] = $this->buildCatalogSection($context['circuits'], $channel);
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
        $now = new \DateTime('now', new \DateTimeZone('Indian/Reunion'));
        $jours = ['Sunday' => 'dimanche', 'Monday' => 'lundi', 'Tuesday' => 'mardi', 'Wednesday' => 'mercredi', 'Thursday' => 'jeudi', 'Friday' => 'vendredi', 'Saturday' => 'samedi'];
        $mois = ['January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril', 'May' => 'mai', 'June' => 'juin', 'July' => 'juillet', 'August' => 'août', 'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre'];
        $today = ($jours[$now->format('l')] ?? $now->format('l')) . ' ' . $now->format('d') . ' ' . ($mois[$now->format('F')] ?? $now->format('F')) . ' ' . $now->format('Y');
        $base = "=== IDENTITÉ ===\nTu es l'assistant de réservation de {$clubName}, un club de vol ULM / aviation légère.";
        $base .= "\nNous sommes le {$today}.";

        if ($channel === 'voice') {
            $base .= "\nTu parles au téléphone. Sois chaleureux, concis, naturel. Ne lis pas de codes internes.";
        } else {
            $base .= "\nTu réponds par email. Sois professionnel, structuré. Tu peux inclure des détails.";
        }

        return $base;
    }

    private function buildCatalogSection(array $circuits, string $channel): string
    {
        $lines = ["=== CATALOGUE ==="];

        $filtered = array_filter($circuits, function (array $c) use ($channel) {
            if ($channel === 'voice') {
                $nature = $c['nature'] ?? '';
                return stripos($nature, 'Local') !== false && stripos($nature, 'Onéreux') !== false;
            }
            return true;
        });

        if (empty($filtered)) {
            $lines[] = "Aucune prestation disponible.";
            return implode("\n", $lines);
        }

        foreach ($filtered as $c) {
            $duree = $this->formatDuration($c['duree'] ?? null);
            $prix = $c['prix'] !== null ? number_format($c['prix'], 2, ',', ' ') . ' euros' : 'sur devis';
            $lines[] = "- {$c['nom']} — Durée : {$duree} — Prix : {$prix}";
        }

        $lines[] = "";
        $lines[] = "Table de correspondance nom ↔ code (usage interne, ne pas dire au client) :";
        foreach ($filtered as $c) {
            $lines[] = "  \"{$c['nom']}\" = {$c['code']}";
        }

        return implode("\n", $lines);
    }

    private function formatDuration(?string $timeStr): string
    {
        if (!$timeStr) {
            return 'non définie';
        }

        $parts = explode(':', $timeStr);
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);

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

    private function buildScheduleSection(array $horaires): string
    {
        $tz = $horaires['timezone'] ?? 'Indian/Reunion';
        $localTz = new \DateTimeZone($tz);

        $minSpoken = '?';
        if ($horaires['min'] ?? null) {
            $dt = new \DateTime('today ' . $horaires['min'], new \DateTimeZone('UTC'));
            $dt->setTimezone($localTz);
            $minSpoken = $this->spokenTime($dt);
        }
        $maxSpoken = '?';
        if ($horaires['max'] ?? null) {
            $dt = new \DateTime('today ' . $horaires['max'], new \DateTimeZone('UTC'));
            $dt->setTimezone($localTz);
            $maxSpoken = $this->spokenTime($dt);
        }

        return "=== HORAIRES ===\nLe club est ouvert de {$minSpoken} à {$maxSpoken} (heure locale).";
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

    private function buildOptionsSection(array $options): string
    {
        $lines = ["=== OPTIONS DISPONIBLES ==="];
        foreach ($options as $o) {
            $prix = $o['prix'] !== null ? number_format($o['prix'], 2, ',', ' ') . ' euros' : 'inclus';
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
            "- Quand tu annonces des horaires, dis-les de façon naturelle : par exemple « 6 heures » ou « 6 heures 35 ».",
            "- Attendre la confirmation explicite du client avant d'enregistrer la demande.",
            "- IMPORTANT : Tu ne confirmes PAS la réservation toi-même. Tu transmets la demande à l'équipe du club qui validera et recontactera le client. Dis bien au client qu'un membre de l'équipe le rappellera pour confirmer.",
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
