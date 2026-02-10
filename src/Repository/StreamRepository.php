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
        return $this->createQueryBuilder('s')
            ->where('s.status = :status')
            ->setParameter('status', 'live')
            ->orderBy('s.viewers', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTopStreams(int $limit = 8): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.status = :status')
            ->setParameter('status', 'live')
            ->orderBy('s.viewers', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }


public function searchByTitleOrId(?string $query): array
{
    $qb = $this->createQueryBuilder('s');

    if ($query) {
        $qb
            ->andWhere('s.title LIKE :q OR s.id = :id')
            ->setParameter('q', '%' . $query . '%')
            ->setParameter('id', ctype_digit($query) ? (int)$query : 0);
    }

    return $qb
        ->orderBy('s.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}

}
