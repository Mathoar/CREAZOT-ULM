<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Résout OIDC_ADMIN / OIDC_USER en se basant sur la table user_client_role + role.
 *
 * Logique :
 *  - ROLE_SUPER_ADMIN → bypass total
 *  - OIDC_ADMIN → l'utilisateur a un rôle avec code='admin' pour le client courant
 *  - OIDC_USER  → l'utilisateur a une entrée pour le client courant
 */
class TenantRoleVoter extends Voter
{
    private const SUPPORTED = ['OIDC_ADMIN', 'OIDC_USER'];

    private array $cache = [];

    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, self::SUPPORTED, true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }

        $clientId = $request->headers->get('X-Client-Id');
        if (!$clientId) {
            return false;
        }

        $roleCode = $this->resolveRoleCode($user, (int) $clientId);

        if ($roleCode === null) {
            return false;
        }

        return match ($attribute) {
            'OIDC_USER' => true,
            'OIDC_ADMIN' => $roleCode === 'admin',
            default => false,
        };
    }

    private function resolveRoleCode(User $user, int $clientId): ?string
    {
        $userId = (string) $user->getId();
        $cacheKey = $userId . ':' . $clientId;

        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        $roleCode = $this->connection->fetchOne(
            'SELECT r.code FROM user_client_role ucr JOIN role r ON r.id = ucr.role_id WHERE ucr.user_id = :uid AND ucr.client_id = :cid LIMIT 1',
            ['uid' => $userId, 'cid' => $clientId]
        );

        $this->cache[$cacheKey] = $roleCode ?: null;

        return $this->cache[$cacheKey];
    }
}
