<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Permission;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;

class RbacInitializer
{
    private const ROLES = [
        'admin'        => 'Administrateur',
        'exploitation' => 'Exploitation',
        'secretariat'  => 'Secrétariat',
        'pilote'       => 'Pilote',
        'securite'     => 'Sécurité',
        'lecture'      => 'Lecture seule',
    ];

    /**
     * Format : [resource => [can_read, can_write]]
     * Les resources non listées pour un rôle sont [false, false].
     */
    private const PERMISSIONS = [
        'admin' => [
            'agenda'               => [true, true],
            'reservations'         => [true, true],
            'prestations'          => [true, true],
            'vols'                 => [true, true],
            'passagers'            => [true, true],
            'commercial'           => [true, true],
            'pilotes'              => [true, true],
            'aeronefs'             => [true, true],
            'formations'           => [true, true],
            'manex'                => [true, true],
            'evenements_securite'  => [true, true],
            'statistiques'         => [true, true],
            'configuration'        => [true, true],
        ],
        'exploitation' => [
            'agenda'               => [true, true],
            'reservations'         => [true, true],
            'prestations'          => [true, true],
            'vols'                 => [true, true],
            'passagers'            => [true, true],
            'commercial'           => [true, true],
            'pilotes'              => [true, false],
            'aeronefs'             => [true, false],
            'formations'           => [true, true],
            'manex'                => [true, false],
            'evenements_securite'  => [true, true],
            'statistiques'         => [true, false],
            'configuration'        => [false, false],
        ],
        'secretariat' => [
            'agenda'               => [true, true],
            'reservations'         => [true, true],
            'prestations'          => [true, false],
            'vols'                 => [true, false],
            'passagers'            => [true, true],
            'commercial'           => [true, true],
            'pilotes'              => [true, false],
            'aeronefs'             => [false, false],
            'formations'           => [false, false],
            'manex'                => [false, false],
            'evenements_securite'  => [false, false],
            'statistiques'         => [false, false],
            'configuration'        => [false, false],
        ],
        'pilote' => [
            'agenda'               => [true, false],
            'reservations'         => [true, false],
            'prestations'          => [true, true],
            'vols'                 => [true, true],
            'passagers'            => [true, false],
            'commercial'           => [false, false],
            'pilotes'              => [false, false],
            'aeronefs'             => [true, false],
            'formations'           => [true, false],
            'manex'                => [true, false],
            'evenements_securite'  => [true, true],
            'statistiques'         => [false, false],
            'configuration'        => [false, false],
        ],
        'securite' => [
            'agenda'               => [true, false],
            'reservations'         => [false, false],
            'prestations'          => [false, false],
            'vols'                 => [true, false],
            'passagers'            => [false, false],
            'commercial'           => [false, false],
            'pilotes'              => [true, false],
            'aeronefs'             => [true, false],
            'formations'           => [true, true],
            'manex'                => [true, true],
            'evenements_securite'  => [true, true],
            'statistiques'         => [true, false],
            'configuration'        => [false, false],
        ],
        'lecture' => [
            'agenda'               => [true, false],
            'reservations'         => [true, false],
            'prestations'          => [true, false],
            'vols'                 => [true, false],
            'passagers'            => [true, false],
            'commercial'           => [true, false],
            'pilotes'              => [true, false],
            'aeronefs'             => [true, false],
            'formations'           => [true, false],
            'manex'                => [true, false],
            'evenements_securite'  => [true, false],
            'statistiques'         => [true, false],
            'configuration'        => [false, false],
        ],
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function initialize(): void
    {
        $roleRepo = $this->em->getRepository(Role::class);

        foreach (self::ROLES as $code => $label) {
            $existing = $roleRepo->findOneBy(['code' => $code]);
            if ($existing) {
                continue;
            }

            $role = new Role();
            $role->setCode($code);
            $role->setLabel($label);
            $role->setIsSystem(true);

            $perms = self::PERMISSIONS[$code] ?? [];
            foreach (PermissionChecker::RESOURCES as $resource) {
                $p = new Permission();
                $p->setResource($resource);
                $p->setCanRead($perms[$resource][0] ?? false);
                $p->setCanWrite($perms[$resource][1] ?? false);
                $role->addPermission($p);
            }

            $this->em->persist($role);
        }

        $this->em->flush();
    }
}
