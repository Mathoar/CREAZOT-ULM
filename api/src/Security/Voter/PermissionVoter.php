<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Service\PermissionChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Résout les attributs PERMISSION_READ_xxx et PERMISSION_WRITE_xxx.
 *
 * Exemples :
 *   is_granted("PERMISSION_READ_aeronefs")
 *   is_granted("PERMISSION_WRITE_configuration")
 */
class PermissionVoter extends Voter
{
    private const PREFIX_READ = 'PERMISSION_READ_';
    private const PREFIX_WRITE = 'PERMISSION_WRITE_';

    public function __construct(
        private readonly PermissionChecker $checker,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return str_starts_with($attribute, self::PREFIX_READ)
            || str_starts_with($attribute, self::PREFIX_WRITE);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if (str_starts_with($attribute, self::PREFIX_READ)) {
            $resource = substr($attribute, strlen(self::PREFIX_READ));
            return $this->checker->canRead($user, $resource);
        }

        if (str_starts_with($attribute, self::PREFIX_WRITE)) {
            $resource = substr($attribute, strlen(self::PREFIX_WRITE));
            return $this->checker->canWrite($user, $resource);
        }

        return false;
    }
}
