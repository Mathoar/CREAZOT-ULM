<?php

declare(strict_types=1);

namespace App\Doctrine\Orm\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Contact;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * When ?client=<id> is present, return only contacts enabled
 * for that client via the client_contact pivot table.
 * Without the parameter, all contacts are returned (super_admin CRUD).
 */
final readonly class ContactClientFilterExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private RequestStack $requestStack) {}

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (Contact::class !== $resourceClass) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $clientId = $request?->query->get('client');

        if (!$clientId) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $clientAlias = $queryNameGenerator->generateJoinAlias('client');

        $queryBuilder
            ->innerJoin('App\Entity\Client', $clientAlias, 'WITH', \sprintf('%s.id = :contactClientId', $clientAlias))
            ->andWhere(\sprintf('%s MEMBER OF %s.contacts', $rootAlias, $clientAlias))
            ->setParameter('contactClientId', (int) $clientId);
    }
}
