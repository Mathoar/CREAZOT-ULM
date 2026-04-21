<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Briefing;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

/**
 * Crée automatiquement un Briefing vide associé à chaque Client nouvellement créé.
 * Garantit l'invariant : 1 Briefing par Client (jamais null côté API).
 */
#[AsDoctrineListener(event: Events::postPersist)]
final class ClientBriefingSubscriber
{
    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Client) {
            return;
        }

        if ($entity->getBriefing() !== null) {
            return;
        }

        $em = $args->getObjectManager();

        $briefing = new Briefing();
        $briefing->setOwnerClient($entity);
        $briefing->setClient($entity);
        $briefing->setShowMap(true);

        $em->persist($briefing);
        $em->flush();
    }
}
