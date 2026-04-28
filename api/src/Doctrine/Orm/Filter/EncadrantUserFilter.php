<?php

declare(strict_types=1);

namespace App\Doctrine\Orm\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

final class EncadrantUserFilter extends AbstractFilter
{
    public function getDescription(string $resourceClass): array
    {
        return [
            'encadrant' => [
                'property' => 'encadrant',
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if ('encadrant' !== $property || null === $value) {
            return;
        }

        if (!filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $profilAlias = $queryNameGenerator->generateJoinAlias('profilPilote');
        $pqAlias = $queryNameGenerator->generateJoinAlias('pilotQualifications');
        $qualAlias = $queryNameGenerator->generateJoinAlias('qualification');

        $queryBuilder
            ->innerJoin(\sprintf('%s.profilPilote', $alias), $profilAlias)
            ->innerJoin(\sprintf('%s.pilotQualifications', $profilAlias), $pqAlias)
            ->innerJoin(\sprintf('%s.qualification', $pqAlias), $qualAlias)
            ->andWhere(\sprintf('%s.encadrant = :encadrant_val', $qualAlias))
            ->setParameter('encadrant_val', true);
    }
}
