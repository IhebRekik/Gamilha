<?php

namespace App\Repository;

use App\Entity\Stream;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StreamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stream::class);
    }

    public function findLiveStreams(): array
    {
        // Exemple si tu ajoutes un champ isLive:boolean
        return $this->findBy(['isLive' => true], ['viewers' => 'DESC']);
    }

    public function countLive(): int
    {
        return $this->count(['isLive' => true]);
    }

    public function getTopStreams(int $limit = 5): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.viewers', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}