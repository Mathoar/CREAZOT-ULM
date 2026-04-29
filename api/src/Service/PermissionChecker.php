<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Permission;
use App\Entity\User;
use App\Entity\UserClientRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class PermissionChecker
{
    public const RESOURCES = [
        'agenda',
        'reservations',
        'prestations',
        'vols',
        'passagers',
        'commercial',
        'pilotes',
        'aeronefs',
        'formations',
        'manex',
        'evenements_securite',
        'statistiques',
        'configuration',
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $requestStack,
        private readonly CacheInterface $cache,
    ) {}

    public function canRead(User $user, string $resource): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $permissions = $this->getPermissionsForCurrentTenant($user);
        return $permissions[$resource]['read'] ?? false;
    }

    public function canWrite(User $user, string $resource): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $permissions = $this->getPermissionsForCurrentTenant($user);
        return $permissions[$resource]['write'] ?? false;
    }

    /**
     * @return array<string, array{read: bool, write: bool}>
     */
    public function getAllPermissions(User $user, ?int $clientId = null): array
    {
        if ($this->isSuperAdmin($user)) {
            $all = [];
            foreach (self::RESOURCES as $r) {
                $all[$r] = ['read' => true, 'write' => true];
            }
            return $all;
        }

        $cid = $clientId ?? $this->getCurrentClientId();
        if (!$cid) {
            return $this->emptyPermissions();
        }

        return $this->loadPermissions($user, $cid);
    }

    public function getRoleCode(User $user, ?int $clientId = null): ?string
    {
        if ($this->isSuperAdmin($user)) {
            return 'super_admin';
        }

        $cid = $clientId ?? $this->getCurrentClientId();
        if (!$cid) {
            return null;
        }

        $ucr = $this->em->getRepository(UserClientRole::class)->findOneBy([
            'user' => $user,
            'client' => $cid,
        ]);

        return $ucr?->getRoleCode();
    }

    public function getRoleLabel(User $user, ?int $clientId = null): ?string
    {
        if ($this->isSuperAdmin($user)) {
            return 'Super Admin';
        }

        $cid = $clientId ?? $this->getCurrentClientId();
        if (!$cid) {
            return null;
        }

        $ucr = $this->em->getRepository(UserClientRole::class)->findOneBy([
            'user' => $user,
            'client' => $cid,
        ]);

        return $ucr?->getRole()?->getLabel();
    }

    private function isSuperAdmin(User $user): bool
    {
        return in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true);
    }

    private function getCurrentClientId(): ?int
    {
        $request = $this->requestStack->getCurrentRequest();
        $clientId = $request?->headers->get('X-Client-Id');

        return $clientId ? (int) $clientId : null;
    }

    /**
     * @return array<string, array{read: bool, write: bool}>
     */
    private function getPermissionsForCurrentTenant(User $user): array
    {
        $clientId = $this->getCurrentClientId();
        if (!$clientId) {
            return $this->emptyPermissions();
        }

        return $this->loadPermissions($user, $clientId);
    }

    /**
     * @return array<string, array{read: bool, write: bool}>
     */
    private function loadPermissions(User $user, int $clientId): array
    {
        $userId = (string) $user->getId();
        $cacheKey = "rbac_{$userId}_{$clientId}";

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($user, $clientId): array {
            $item->expiresAfter(3600);

            $ucr = $this->em->getRepository(UserClientRole::class)->findOneBy([
                'user' => $user,
                'client' => $clientId,
            ]);

            if (!$ucr || !$ucr->getRole()) {
                return $this->emptyPermissions();
            }

            $permissions = $this->em->getRepository(Permission::class)->findBy([
                'role' => $ucr->getRole(),
            ]);

            $result = $this->emptyPermissions();
            foreach ($permissions as $perm) {
                $result[$perm->getResource()] = [
                    'read' => $perm->getCanRead(),
                    'write' => $perm->getCanWrite(),
                ];
            }

            return $result;
        });
    }

    /**
     * @return array<string, array{read: bool, write: bool}>
     */
    private function emptyPermissions(): array
    {
        $empty = [];
        foreach (self::RESOURCES as $r) {
            $empty[$r] = ['read' => false, 'write' => false];
        }
        return $empty;
    }

    public function invalidateCache(string $userId, int $clientId): void
    {
        $this->cache->delete("rbac_{$userId}_{$clientId}");
    }
}
