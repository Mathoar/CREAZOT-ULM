<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\ManexSection;
use App\Service\HtmlSanitizer;
use App\Service\Manex\ManexSections;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class ManexBootstrapSubscriber
{
    public function __construct(
        private readonly HtmlSanitizer $htmlSanitizer,
    ) {}

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->sanitize($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->sanitize($args);
    }

    private function sanitize(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof ManexSection) {
            return;
        }

        if ($entity->getIntroHtml()) {
            $entity->setIntroHtml($this->htmlSanitizer->sanitize($entity->getIntroHtml()));
        }
        if ($entity->getCustomHtml()) {
            $entity->setCustomHtml($this->htmlSanitizer->sanitize($entity->getCustomHtml()));
        }
    }
}
