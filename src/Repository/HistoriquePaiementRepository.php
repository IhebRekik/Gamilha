<?php

namespace App\Repository;

use App\Entity\HistoriquePaiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoriquePaiement>
 */
class HistoriquePaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriquePaiement::class);
    }
// src/Repository/HistoriquePaiementRepository.php

public function getAllPayments(): array
{
    return $this->createQueryBuilder('h')
        ->select('h.createdAt, h.montant')
        ->getQuery()
        ->getResult();
}

public function getMonthlyRevenue(): float
{
    return $this->createQueryBuilder('h')
        ->select("SUM(h.montant)")
        ->andWhere("MONTH(h.createdAt) = MONTH(CURRENT_DATE())")
        ->getQuery()
        ->getSingleScalarResult() ?? 0;
}

public function countByType(): array
{
    return $this->createQueryBuilder('h')
        ->select('a.type as type')
        ->addSelect('COUNT(h.id) as total')
        ->join('h.abonnement', 'a')
        ->groupBy('a.type')
        ->getQuery()
        ->getResult();
}
//    /**
//     * @return HistoriquePaiement[] Returns an array of HistoriquePaiement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HistoriquePaiement
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
