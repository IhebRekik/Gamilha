<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * @return Evenement[]
     */
    public function findBySearchAndSort(string $search = '', string $sortBy = 'idEvenement', string $sortOrder = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($search !== '') {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('e.nom', ':search'),
                    $qb->expr()->like('e.jeu', ':search'),
                    $qb->expr()->like('e.description', ':search'),
                    $qb->expr()->like('e.typeEvenement', ':search'),
                    $qb->expr()->like('e.statut', ':search')
                )
            )->setParameter('search', '%' . $search . '%');
        }

        $allowedSorts = ['idEvenement', 'nom', 'jeu', 'typeEvenement', 'dateDebut', 'dateFin', 'statut'];
        if (!\in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'idEvenement';
        }
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        $qb->orderBy('e.' . $sortBy, $sortOrder);

        return $qb->getQuery()->getResult();
    }
}
