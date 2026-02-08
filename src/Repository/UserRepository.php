<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Nombre total d'utilisateurs
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Utilisateurs avec rôle admin
     */
    public function findAdmins(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_ADMIN"%')
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Utilisateurs récents (ex: derniers 30 jours)
     */
    public function findRecent(int $days = 30): array
    {
        $date = new \DateTime("-{$days} days");

        return $this->createQueryBuilder('u')
            ->where('u.createdAt >= :date') // ← suppose que tu as ajouté createdAt
            ->setParameter('date', $date)
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }public function findByNameOrEmail(string $term): array
{
    return $this->createQueryBuilder('u')
        ->where('u.name LIKE :term OR u.email LIKE :term')
        ->setParameter('term', '%' . $term . '%')
        ->orderBy('u.name', 'ASC')
        ->getQuery()
        ->getResult();
}
}