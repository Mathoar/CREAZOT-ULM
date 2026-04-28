<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Training;
use App\Entity\Progress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Symfony\EventListener\EventPriorities;

class TrainingCreateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => ['onTrainingCreate', EventPriorities::POST_WRITE]];
    }

    public function onTrainingCreate(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$entity instanceof Training || $method !== 'POST') {
            return;
        }

        $programme = $entity->getProgramme();
        if (!$programme) {
            return;
        }

        if (!$entity->getProgresses()->isEmpty()) {
            return;
        }

        foreach ($programme->getProgrammeLessons() as $programmeLesson) {
            $lesson = $programmeLesson->getLesson();
            if (!$lesson) {
                continue;
            }

            $progress = new Progress();
            $progress->setTraining($entity);
            $progress->setLesson($lesson);
            $progress->setNiveau(Progress::NIVEAU_NON_ABORDE);

            if ($entity->getClient()) {
                $progress->setClient($entity->getClient());
            }

            $this->em->persist($progress);
        }

        $this->em->flush();
    }
}
