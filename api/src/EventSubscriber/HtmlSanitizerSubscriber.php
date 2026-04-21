<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Briefing;
use App\Entity\Circuit;
use App\Service\HtmlSanitizer;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Nettoie automatiquement les contenus HTML (briefing) avant persistence,
 * en supprimant scripts, iframes et attributs dangereux.
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class HtmlSanitizerSubscriber
{
    public function __construct(private HtmlSanitizer $sanitizer) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Briefing) {
            $entity->setHtml($this->sanitizer->sanitize($entity->getHtml()));
        }

        if ($entity instanceof Circuit) {
            $entity->setBriefingHtml($this->sanitizer->sanitize($entity->getBriefingHtml()));
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Briefing && $args->hasChangedField('html')) {
            $clean = $this->sanitizer->sanitize($args->getNewValue('html'));
            $args->setNewValue('html', $clean);
        }

        if ($entity instanceof Circuit && $args->hasChangedField('briefingHtml')) {
            $clean = $this->sanitizer->sanitize($args->getNewValue('briefingHtml'));
            $args->setNewValue('briefingHtml', $clean);
        }
    }
}
