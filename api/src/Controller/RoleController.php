<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\UserClientRole;
use App\Service\PermissionChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RoleController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PermissionChecker $permissionChecker,
    ) {}

    #[Route('/admin/roles/{id}', name: 'admin_role_update', methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function update(int $id, Request $request): JsonResponse
    {
        $role = $this->em->getRepository(Role::class)->find($id);
        if (!$role) {
            return new JsonResponse(['error' => 'Rôle introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['label'])) {
            $role->setLabel($data['label']);
        }

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $existingPerms = [];
            foreach ($role->getPermissions() as $perm) {
                $existingPerms[$perm->getResource()] = $perm;
            }

            foreach ($data['permissions'] as $permData) {
                $resource = $permData['resource'] ?? null;
                if (!$resource) {
                    continue;
                }

                if (isset($existingPerms[$resource])) {
                    $perm = $existingPerms[$resource];
                } else {
                    $perm = new Permission();
                    $perm->setResource($resource);
                    $role->addPermission($perm);
                }

                if (isset($permData['canRead'])) {
                    $perm->setCanRead((bool) $permData['canRead']);
                }
                if (isset($permData['canWrite'])) {
                    $perm->setCanWrite((bool) $permData['canWrite']);
                }
            }
        }

        $this->em->flush();

        $this->invalidateCacheForRole($role);

        $permsOut = [];
        foreach ($role->getPermissions() as $p) {
            $permsOut[] = [
                'id' => $p->getId(),
                'resource' => $p->getResource(),
                'canRead' => $p->getCanRead(),
                'canWrite' => $p->getCanWrite(),
            ];
        }

        return new JsonResponse([
            'id' => $role->getId(),
            'code' => $role->getCode(),
            'label' => $role->getLabel(),
            'isSystem' => $role->getIsSystem(),
            'permissions' => $permsOut,
        ]);
    }

    private function invalidateCacheForRole(Role $role): void
    {
        $ucrs = $this->em->getRepository(UserClientRole::class)->findBy(['role' => $role]);
        foreach ($ucrs as $ucr) {
            $userId = (string) $ucr->getUser()->getId();
            $clientId = $ucr->getClient()->getId();
            $this->permissionChecker->invalidateCache($userId, $clientId);
        }
    }
}
