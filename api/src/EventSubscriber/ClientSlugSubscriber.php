<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Génère automatiquement un slug à la création d'un Client si aucun n'est fourni.
 * Le slug n'est pas exposé dans le formulaire d'édition côté admin : il est donc
 * obligatoirement dérivé du nom du client. Il conditionne l'affichage du lien
 * "Formulaire passager" dans le UserMenu et les URLs publiques /{slug}.
 *
 * En cas de collision (colonne unique), suffixe incrémental : -2, -3, ...
 */
#[AsDoctrineListener(event: Events::prePersist)]
final class ClientSlugSubscriber
{
    public function __construct(private readonly ClientRepository $clientRepository)
    {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Client) {
            return;
        }

        $current = $entity->getSlug();
        if ($current !== null && trim($current) !== '') {
            return;
        }

        $name = $entity->getName();
        if ($name === null || trim($name) === '') {
            return;
        }

        $slugger = new AsciiSlugger();
        $base = strtolower((string) $slugger->slug($name));
        if ($base === '') {
            return;
        }

        $candidate = $base;
        $i = 2;
        while ($this->clientRepository->findOneBy(['slug' => $candidate]) !== null) {
            $candidate = $base . '-' . $i;
            $i++;
        }

        $entity->setSlug($candidate);
    }
}
