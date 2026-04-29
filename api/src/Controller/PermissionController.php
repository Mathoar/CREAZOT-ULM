<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\PermissionChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PermissionController extends AbstractController
{
    public function __construct(
        private readonly PermissionChecker $checker,
    ) {}

    #[Route('/api/me/permissions', name: 'api_me_permissions', methods: ['GET'])]
    #[IsGranted('OIDC_USER')]
    public function __invoke(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $roleCode = $this->checker->getRoleCode($user);
        $roleLabel = $this->checker->getRoleLabel($user);
        $permissions = $this->checker->getAllPermissions($user);

        return new JsonResponse([
            'role' => $roleCode,
            'roleLabel' => $roleLabel,
            'permissions' => $permissions,
        ]);
    }
}
