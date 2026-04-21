<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Briefing;
use App\Entity\Circuit;
use App\Entity\MediaObject;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Supprime les MediaObject orphelins lorsque la relation briefingImage / headerImage
 * d'un Circuit ou d'un Briefing passe à null (suppression explicite par l'utilisateur)
 * ou est remplacée par un autre MediaObject.
 *
 * Vich Uploader (delete_on_remove: true, cf. config/packages/vich_uploader.yaml)
 * supprime alors automatiquement le fichier physique associé.
 *
 * Implémentation :
 *  - preUpdate : on lit le changeset Doctrine pour capturer l'ancien MediaObject
 *  - postFlush : on supprime les orphelins capturés (re-flush) après le UPDATE principal
 *
 * Limitation connue : si le même MediaObject est partagé par plusieurs entités
 * (peu probable vu l'usage actuel : un Circuit et un Briefing ont chacun leur
 * propre image), il sera quand même supprimé. Les autres entités auront alors
 * une référence cassée. Ce cas n'est pas géré pour l'instant.
 */
#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::postFlush)]
final class MediaObjectOrphanCleanupSubscriber
{
    /** @var array<int, MediaObject> */
    private array $orphansToRemove = [];

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Circuit) {
            $this->captureOrphan($args, 'briefingImage');
        } elseif ($entity instanceof Briefing) {
            $this->captureOrphan($args, 'headerImage');
        }
    }

    private function captureOrphan(PreUpdateEventArgs $args, string $field): void
    {
        if (!$args->hasChangedField($field)) {
            return;
        }

        $old = $args->getOldValue($field);
        $new = $args->getNewValue($field);

        if (!$old instanceof MediaObject) {
            return;
        }
        if ($new instanceof MediaObject && $new->getId() === $old->getId()) {
            return;
        }

        $this->orphansToRemove[$old->getId()] = $old;
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if (empty($this->orphansToRemove)) {
            return;
        }

        $orphans = $this->orphansToRemove;
        $this->orphansToRemove = [];

        $em = $args->getObjectManager();
        foreach ($orphans as $orphan) {
            $managed = $em->find(MediaObject::class, $orphan->getId());
            if ($managed === null) {
                continue;
            }
            $em->remove($managed);
        }

        $em->flush();
    }
}
