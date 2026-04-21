<?php

declare(strict_types=1);

namespace App\Service\Sms;

/**
 * Sanitise un texte pour maximiser la chance qu'il reste encodable en GSM-7
 * (160 chars/segment au lieu de 70 chars/segment en UCS-2).
 *
 * Calcule également le nombre de segments et le surcoût estimé.
 *
 * Référence : 3GPP TS 23.038 (alphabet GSM-7 standard + table d'extension).
 */
final class GsmSanitizer
{
    public const ENCODING_GSM7 = 'GSM-7';
    public const ENCODING_UCS2 = 'UCS-2';

    /**
     * Caractères de l'alphabet GSM-7 standard (1 unité chacun).
     * Source : 3GPP TS 23.038 Table 6.2.1.1.
     */
    private const GSM7_BASIC =
        "@£\$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ ÆæßÉ"
        . " !\"#¤%&'()*+,-./0123456789:;<=>?"
        . "¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§"
        . "¿abcdefghijklmnopqrstuvwxyzäöñüà";

    /**
     * Table d'extension GSM-7 (chaque caractère compte pour 2 unités, à cause de l'octet escape).
     */
    private const GSM7_EXTENSION = "^{}\\[~]|€";

    /**
     * Mapping des caractères non-GSM-7 fréquents vers leur équivalent GSM-7 / ASCII.
     * Permet d'éviter le passage en UCS-2 (qui divise la capacité par 2).
     */
    private const REPLACEMENTS = [
        // Apostrophes typographiques
        "\u{2018}" => "'", // ‘
        "\u{2019}" => "'", // ’
        "\u{201A}" => "'", // ‚
        "\u{201B}" => "'", // ‛
        // Guillemets typographiques
        "\u{201C}" => '"', // “
        "\u{201D}" => '"', // ”
        "\u{201E}" => '"', // „
        "\u{201F}" => '"', // ‟
        "\u{00AB}" => '"', // «
        "\u{00BB}" => '"', // »
        // Tirets / ponctuation
        "\u{2013}" => '-', // –
        "\u{2014}" => '-', // —
        "\u{2015}" => '-', // ―
        "\u{2026}" => '...', // …
        "\u{00A0}" => ' ',  // espace insécable
        "\u{202F}" => ' ',  // espace fine insécable
        "\u{2022}" => '*',  // •
        "\u{00B7}" => '.',  // ·
        // Ligatures
        "\u{0153}" => 'oe', // œ
        "\u{0152}" => 'OE', // Œ
        "\u{00E6}" => 'ae', // æ → conservé en GSM-7 mais on uniformise pour FR
        "\u{00C6}" => 'AE',
        // Lettres accentuées non-GSM-7 (conservation du sens, perte de l'accent)
        'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'ā' => 'a', 'ă' => 'a',
        'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ā' => 'A', 'Ă' => 'A',
        'ç' => 'c', 'č' => 'c', 'ć' => 'c',
        'Ć' => 'C', 'Č' => 'C',
        'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ê' => 'e', 'ë' => 'e',
        'Ê' => 'E', 'Ë' => 'E', 'È' => 'E',
        'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i',
        'Î' => 'I', 'Ï' => 'I', 'Í' => 'I', 'Ì' => 'I',
        'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ō' => 'o',
        'Ó' => 'O', 'Ô' => 'O', 'Ò' => 'O',
        'ú' => 'u', 'û' => 'u', 'ū' => 'u',
        'Ú' => 'U', 'Û' => 'U', 'Ù' => 'U',
        'ý' => 'y', 'ÿ' => 'y',
        'Ý' => 'Y', 'Ÿ' => 'Y',
        'ñ' => 'n', // déjà GSM-7 mais pour cohérence si jamais
        // Symboles courants
        "\u{2122}" => '(TM)', // ™
        "\u{00A9}" => '(c)',  // ©
        "\u{00AE}" => '(R)',  // ®
        "\u{00B0}" => 'deg',  // °
        "\u{00B1}" => '+/-',  // ±
        "\u{00D7}" => 'x',    // ×
        "\u{00F7}" => '/',    // ÷
        "\u{20AC}" => 'EUR',  // € (présent en table d'extension mais coûte 2 unités → on remplace)
    ];

    /**
     * Sanitise le texte et retourne le résultat + métadonnées.
     *
     * @return array{
     *     original: string,
     *     sanitized: string,
     *     encoding: string,
     *     length: int,
     *     segments: int,
     *     replacedChars: int,
     *     unsupportedChars: array<string>,
     * }
     */
    public function analyze(string $body): array
    {
        $original = $body;
        $sanitized = strtr($body, self::REPLACEMENTS);

        $replacedChars = mb_strlen($original) - mb_strlen($sanitized);
        $replacedChars = max(0, $replacedChars);

        [$encoding, $units, $unsupported] = $this->detectEncoding($sanitized);
        $segments = $this->computeSegments($units, $encoding);

        return [
            'original' => $original,
            'sanitized' => $sanitized,
            'encoding' => $encoding,
            'length' => mb_strlen($sanitized),
            'units' => $units,
            'segments' => $segments,
            'replacedChars' => $replacedChars,
            'unsupportedChars' => array_values(array_unique($unsupported)),
        ];
    }

    /**
     * Sanitise et retourne uniquement le texte transformé.
     */
    public function sanitize(string $body): string
    {
        return strtr($body, self::REPLACEMENTS);
    }

    /**
     * Détecte l'encoding final, calcule le nombre d'unités GSM-7 (avec extension = 2 unités)
     * ou retombe sur UCS-2 si au moins un caractère n'est pas dans GSM-7 + extension.
     *
     * @return array{0: string, 1: int, 2: array<string>}
     */
    private function detectEncoding(string $body): array
    {
        $basic = $this->splitToChars(self::GSM7_BASIC);
        $ext = $this->splitToChars(self::GSM7_EXTENSION);

        $units = 0;
        $unsupported = [];
        $isGsm7 = true;

        foreach ($this->splitToChars($body) as $char) {
            if (in_array($char, $basic, true)) {
                $units++;
            } elseif (in_array($char, $ext, true)) {
                $units += 2;
            } else {
                $isGsm7 = false;
                $unsupported[] = $char;
            }
        }

        if (!$isGsm7) {
            return [self::ENCODING_UCS2, mb_strlen($body), $unsupported];
        }

        return [self::ENCODING_GSM7, $units, []];
    }

    private function computeSegments(int $units, string $encoding): int
    {
        if ($units === 0) {
            return 0;
        }

        if ($encoding === self::ENCODING_GSM7) {
            return $units <= 160 ? 1 : (int) ceil($units / 153);
        }

        return $units <= 70 ? 1 : (int) ceil($units / 67);
    }

    /**
     * @return array<string>
     */
    private function splitToChars(string $str): array
    {
        return preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
