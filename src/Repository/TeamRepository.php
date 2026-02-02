<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function findByNamePartial(string $search): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.name LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countTeams(): int
    {
        return $this->count([]);
    }
}
