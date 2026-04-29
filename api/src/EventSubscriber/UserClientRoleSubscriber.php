<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Client;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\UserClientRole;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Garantit qu'un UserClientRole existe pour chaque relation User↔Client.
 * Si un user est rattaché à un client sans UCR, on en crée un avec le rôle 'pilote' par défaut.
 */
#[AsDoctrineListener(event: Events::onFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
class UserClientRoleSubscriber
{
    private array $pendingUcrs = [];

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            $owner = $collection->getOwner();
            if (!$owner instanceof User) {
                continue;
            }

            $mapping = $collection->getMapping();
            if (($mapping['fieldName'] ?? '') !== 'clients') {
                continue;
            }

            foreach ($collection->getInsertDiff() as $client) {
                if (!$client instanceof Client) {
                    continue;
                }

                $existing = $em->getRepository(UserClientRole::class)->findOneBy([
                    'user' => $owner,
                    'client' => $client,
                ]);

                if (!$existing) {
                    $this->pendingUcrs[] = ['user' => $owner, 'client' => $client];
                }
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if (empty($this->pendingUcrs)) {
            return;
        }

        $em = $args->getObjectManager();
        $defaultRole = $em->getRepository(Role::class)->findOneBy(['code' => 'pilote']);

        if (!$defaultRole) {
            return;
        }

        $toProcess = $this->pendingUcrs;
        $this->pendingUcrs = [];

        foreach ($toProcess as $pending) {
            $existing = $em->getRepository(UserClientRole::class)->findOneBy([
                'user' => $pending['user'],
                'client' => $pending['client'],
            ]);

            if ($existing) {
                continue;
            }

            $ucr = new UserClientRole();
            $ucr->setUser($pending['user']);
            $ucr->setClient($pending['client']);
            $ucr->setRole($defaultRole);
            $em->persist($ucr);
        }

        $em->flush();
    }
}
