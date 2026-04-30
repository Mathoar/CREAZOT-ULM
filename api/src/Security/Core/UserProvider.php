<?php

declare(strict_types=1);

namespace App\Security\Core;

use App\Entity\Client;
use App\Entity\User;
use App\Entity\ProfilPilote;
use App\Entity\CertificatMedical;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @implements AttributesBasedUserProviderInterface<UserInterface|User>
 */
final readonly class UserProvider implements AttributesBasedUserProviderInterface
{
    public function __construct(
        private ManagerRegistry $registry,
        private UserRepository $repository,
        private RequestStack $requestStack,
    ) {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        $manager = $this->registry->getManagerForClass($user::class);
        if (!$manager) {
            throw new UnsupportedUserException(\sprintf('User class "%s" not supported.', $user::class));
        }

        $manager->refresh($user);

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }

    /**
     * Create or update User on login.
     *
     * Keycloak roles mapping:
     *   - super_admin → ROLE_SUPER_ADMIN + ROLE_ADMIN + OIDC_ADMIN (global)
     *   - admin/pilot  → ROLE_USER only (per-client role via UserClientRole + ClientRoleListener)
     */
    public function loadUserByIdentifier(string $identifier, array $attributes = []): UserInterface
    {
        $em = $this->registry->getManagerForClass(User::class);
        $em->clear();

        $user = null;

        if (!empty($attributes['sub'])) {
            $user = $this->repository->findOneBy(['keycloakId' => $attributes['sub']]);
        }

        if (!$user) {
            $user = $this->repository->findOneBy(['email' => $identifier]) ?: new User();
        }

        $user->email = $identifier;
        if (!empty($attributes['sub'])) {
            $user->setKeycloakId($attributes['sub']);
        }

        $user->firstName = $attributes['given_name'] ?? $user->firstName;
        $user->lastName  = $attributes['family_name'] ?? $user->lastName;

        if (!empty($attributes['realm_access']['roles'])) {
            $keycloakRoles = $attributes['realm_access']['roles'];

            if (in_array('super_admin', $keycloakRoles)) {
                $newRoles = ['ROLE_USER', 'OIDC_USER', 'ROLE_ADMIN', 'OIDC_ADMIN', 'ROLE_SUPER_ADMIN'];
            } else {
                $newRoles = ['ROLE_USER', 'OIDC_USER'];
            }

            $user->setRoles($newRoles);
        }

        if (empty($user->getProfilPilote())) {
            $profile = $this->createProfile($user);
            $user->setProfilPilote($profile);
        }

        $this->associateClient($user);

        $this->repository->save($user, true);

        return $user;
    }

    private function associateClient(User $user): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $clientId = $request?->headers->get('X-Client-Id');
        if (!$clientId) {
            return;
        }

        $em = $this->registry->getManagerForClass(Client::class);
        $client = $em?->getRepository(Client::class)->find((int) $clientId);
        if ($client && !$user->hasClient($client)) {
            $user->addClient($client);
        }
    }

    private function createProfile(User $user) :ProfilPilote 
    {
        $now = new \DateTimeImmutable();
        $firstDayOfYear = new \DateTimeImmutable(date('Y') . '-01-01 00:00:00');

        $profile = new ProfilPilote();

        $profile
            ->setPilote($user)
            ->setBirthDate($firstDayOfYear)
            ->setTotalFlightHours(0)
            ->setAvailableByDefault(true)
            ->setCreatedBy($user)
            ->setCreatedAt($now);

        $certificatMedical = $this->getCertificatMedical($profile);
        $profile->setCertificatMedical($certificatMedical);

        return $profile;
    }

    private function getCertificatMedical(ProfilPilote $profile) :CertificatMedical 
    {
        $certificatMedical = new CertificatMedical();

        $certificatMedical
            ->setType('CNCI')
            ->setDateObtention($profile->getBirthDate())
            ->setCreatedBy($profile->getCreatedBy())
            ->setCreatedAt($profile->getCreatedAt());

        return $certificatMedical;
    }
}
