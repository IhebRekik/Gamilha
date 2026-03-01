<?php

namespace App\Repository;

use App\Entity\SocialMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SocialMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialMedia::class);
    }

    public function findRecentPosts(int $limit = 8): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC') // ← suppose createdAt ajouté
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getTotalLikes(): int
    {
        $result = $this->createQueryBuilder('s')
            ->select('SUM(s.likes)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }
}
