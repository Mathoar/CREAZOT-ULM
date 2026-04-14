<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Client;
use App\Entity\IntegrationPattern;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class IntegrationAutoAssignSubscriber implements EventSubscriberInterface
{
    private PropertyAccessor $accessor;

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        $this->accessor = new PropertyAccessor();
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onClientWrite', EventPriorities::POST_WRITE]];
    }

    public function onClientWrite(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$entity instanceof Client || !in_array($method, ['POST', 'PUT', 'PATCH'])) {
            return;
        }

        $patterns = $this->em->getRepository(IntegrationPattern::class)->findBy([
            'active' => true,
        ]);

        $patternsByCapability = [];
        foreach ($patterns as $pattern) {
            $cap = $pattern->getCapability();
            $mod = $pattern->getRequiredModule();
            if (!$cap || !$mod) {
                continue;
            }
            if (!isset($patternsByCapability[$cap])) {
                $patternsByCapability[$cap] = [];
            }
            $patternsByCapability[$cap][] = $pattern;
        }

        $changed = false;

        foreach ($patternsByCapability as $capability => $capPatterns) {
            $requiredModule = $capPatterns[0]->getRequiredModule();

            $moduleActive = false;
            try {
                $moduleActive = (bool) $this->accessor->getValue($entity, $requiredModule);
            } catch (\Throwable) {
                continue;
            }

            $hasPatternForCapability = false;
            foreach ($entity->getIntegrationPatterns() as $existing) {
                if ($existing->getCapability() === $capability) {
                    $hasPatternForCapability = true;
                    break;
                }
            }

            if ($moduleActive && !$hasPatternForCapability) {
                $capPatterns[0]->addClient($entity);
                $changed = true;
            }
        }

        if ($changed) {
            $this->em->flush();
        }
    }
}
