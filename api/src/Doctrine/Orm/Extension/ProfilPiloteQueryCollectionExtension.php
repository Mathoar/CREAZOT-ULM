<?php

declare(strict_types=1);

namespace App\Doctrine\Orm\Extension;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\ProfilPilote;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Restrict ProfilPilote collection:
 *  - non-admin → own profile only
 *  - admin/super_admin on /disponibles → filter by X-Client-Id
 *    (profil_pilotes_list is handled by ProfilPiloteListExtension)
 */
final readonly class ProfilPiloteQueryCollectionExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private Security $security,
        private AuthorizationCheckerInterface $auth,
        private RequestStack $requestStack,
    ) {}

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (
            ProfilPilote::class !== $resourceClass
            || !\in_array($operation?->getName(), ['profil_pilotes_list', 'profil_pilotes_available'], true)
            || !$user = $this->security->getUser()
        ) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        if (!$this->auth->isGranted('OIDC_ADMIN')) {
            $queryBuilder
                ->andWhere(\sprintf('%s.pilote = :user', $rootAlias))
                ->setParameter('user', $user);
            return;
        }

        if ($operation?->getName() === 'profil_pilotes_available') {
            $request = $this->requestStack->getCurrentRequest();
            $clientId = $request?->headers->get('X-Client-Id');

            if ($clientId) {
                $piloteAlias = $queryNameGenerator->generateJoinAlias('pilote');
                $clientsAlias = $queryNameGenerator->generateJoinAlias('clients');
                $queryBuilder
                    ->innerJoin(\sprintf('%s.pilote', $rootAlias), $piloteAlias)
                    ->innerJoin(\sprintf('%s.clients', $piloteAlias), $clientsAlias)
                    ->andWhere(\sprintf('%s.id = :ppClientId', $clientsAlias))
                    ->setParameter('ppClientId', (int) $clientId);
            }
        }
    }
}
