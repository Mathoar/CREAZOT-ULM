<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Génère un shortcode unique pour exposer une réservation sur la page publique /r/{shortcode}.
 *
 * Format : 10 caractères alphanumériques [a-zA-Z0-9] => 62^10 ≈ 8.4×10^17 combinaisons,
 * suffisant pour rendre le bruteforce inviable tout en gardant une URL très courte (idéal SMS).
 */
final class ShortcodeGenerator
{
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const LENGTH = 10;
    private const MAX_ATTEMPTS = 8;

    public function __construct(private EntityManagerInterface $em) {}

    public function generate(): string
    {
        $repo = $this->em->getRepository(Reservation::class);
        $alphabetLen = strlen(self::ALPHABET);

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $code = '';
            for ($i = 0; $i < self::LENGTH; $i++) {
                $code .= self::ALPHABET[random_int(0, $alphabetLen - 1)];
            }

            $exists = $repo->findOneBy(['publicShortcode' => $code]);
            if (!$exists) {
                return $code;
            }
        }

        throw new \RuntimeException('Impossible de générer un shortcode unique après ' . self::MAX_ATTEMPTS . ' tentatives.');
    }
}
