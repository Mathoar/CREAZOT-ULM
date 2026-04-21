<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\MediaObject;
use App\Service\ImageResizer;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

/**
 * Après persistance d'un MediaObject, redimensionne automatiquement le fichier
 * image (JPEG / PNG) s'il dépasse 1920px de large. Sans effet sur les PDF.
 *
 * Le fichier physique est déjà sur disk à ce stade (Vich a fait son upload
 * avant le flush Doctrine), on opère donc en place sur public/media/<filePath>.
 */
#[AsDoctrineListener(event: Events::postPersist)]
final class MediaObjectImageResizeSubscriber
{
    public function __construct(
        private readonly ImageResizer $resizer,
        private readonly string $uploadDir,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof MediaObject) {
            return;
        }

        $filePath = $entity->filePath;
        if (!$filePath) {
            return;
        }

        $fullPath = rtrim($this->uploadDir, '/') . '/' . $filePath;
        if (!is_file($fullPath)) {
            return;
        }

        $this->resizer->resizeIfNeeded($fullPath);
    }
}
