<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Origine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Origine>
 */
class OrigineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Origine::class);
    }

    public function findOneByNameInsensitive(string $value, ?Client $client = null): ?Origine
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('LOWER(o.name) = LOWER(:val)')
            ->setParameter('val', $value);

        if ($client) {
            $qb->andWhere('o.client = :client')
               ->setParameter('client', $client);
        }

        return $qb->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
