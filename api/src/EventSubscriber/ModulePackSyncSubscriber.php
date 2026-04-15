<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Symfony\EventListener\EventPriorities;

class ModulePackSyncSubscriber implements EventSubscriberInterface
{
    private ?array $knownModules = null;

    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['syncModules', EventPriorities::POST_WRITE]];
    }

    public function syncModules(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$entity instanceof Client || $method !== 'PUT') {
            return;
        }

        $category = $entity->getPricingCategory();
        if ($category && $category->getSlug() === 'personnalise') {
            return;
        }

        $activeModules = [];
        foreach ($entity->getModulePacks() as $pack) {
            foreach ($pack->getModules() as $module) {
                $activeModules[] = $module;
            }
        }
        $activeModules = array_unique($activeModules);

        foreach ($this->getKnownModules() as $module) {
            $setter = 'set' . ucfirst($module);
            if (method_exists($entity, $setter)) {
                $entity->{$setter}(in_array($module, $activeModules, true));
            }
        }

        $this->em->persist($entity);
        $this->em->flush();
    }

    /**
     * Introspection dynamique : récupère tous les champs boolean has* de Client.
     * @return string[]
     */
    private function getKnownModules(): array
    {
        if ($this->knownModules !== null) {
            return $this->knownModules;
        }

        $meta = $this->em->getClassMetadata(Client::class);
        $this->knownModules = [];

        foreach ($meta->fieldMappings as $field => $mapping) {
            $type = $mapping['type'] ?? '';
            if ($type === 'boolean' && str_starts_with($field, 'has')) {
                $this->knownModules[] = $field;
            }
        }

        return $this->knownModules;
    }
}
