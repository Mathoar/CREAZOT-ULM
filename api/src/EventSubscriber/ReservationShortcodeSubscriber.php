<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Reservation;
use App\Service\ShortcodeGenerator;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

/**
 * À la création d'une réservation, génère automatiquement un publicShortcode
 * si le shortcode n'est pas déjà défini.
 */
#[AsDoctrineListener(event: Events::prePersist)]
final class ReservationShortcodeSubscriber
{
    public function __construct(private ShortcodeGenerator $generator) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Reservation) {
            return;
        }

        if ($entity->getPublicShortcode()) {
            return;
        }

        $entity->setPublicShortcode($this->generator->generate());
    }
}
