<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 * Redimensionne automatiquement les images uploadées (JPEG / PNG) si leur largeur
 * dépasse MAX_WIDTH. Conserve le ratio. Recompresse en JPEG qualité 85 ou PNG
 * niveau 6. Utilise GD (extension native PHP).
 *
 * Cible : images d'en-tête de briefing, photos circuits, etc.
 * Les PDF et autres formats sont ignorés.
 */
final class ImageResizer
{
    private const MAX_WIDTH = 1920;
    private const JPEG_QUALITY = 85;
    private const PNG_COMPRESSION = 6;

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function resizeIfNeeded(string $filePath): bool
    {
        if (!is_file($filePath) || !is_readable($filePath) || !is_writable($filePath)) {
            return false;
        }

        $info = @getimagesize($filePath);
        if ($info === false) {
            return false;
        }

        [$width, $height, $type] = $info;

        if (!\in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            return false;
        }

        if ($width <= self::MAX_WIDTH) {
            return false;
        }

        $newWidth = self::MAX_WIDTH;
        $newHeight = (int) round($height * ($newWidth / $width));

        try {
            $src = match ($type) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath),
                IMAGETYPE_PNG  => @imagecreatefrompng($filePath),
                default        => null,
            };

            if ($src === null || $src === false) {
                return false;
            }

            $dst = imagecreatetruecolor($newWidth, $newHeight);

            if ($type === IMAGETYPE_PNG) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled(
                $dst, $src,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $width, $height
            );

            $sizeBefore = filesize($filePath);

            $ok = match ($type) {
                IMAGETYPE_JPEG => imagejpeg($dst, $filePath, self::JPEG_QUALITY),
                IMAGETYPE_PNG  => imagepng($dst, $filePath, self::PNG_COMPRESSION),
                default        => false,
            };

            imagedestroy($src);
            imagedestroy($dst);

            if (!$ok) {
                return false;
            }

            $sizeAfter = filesize($filePath);

            $this->logger->info('[ImageResizer] image redimensionnée', [
                'path'     => $filePath,
                'from'     => sprintf('%dx%d', $width, $height),
                'to'       => sprintf('%dx%d', $newWidth, $newHeight),
                'sizeKbBefore' => $sizeBefore !== false ? (int) round($sizeBefore / 1024) : null,
                'sizeKbAfter'  => $sizeAfter !== false ? (int) round($sizeAfter / 1024) : null,
            ]);

            return true;
        } catch (\Throwable $e) {
            $this->logger->error('[ImageResizer] échec redimensionnement', [
                'path'  => $filePath,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
