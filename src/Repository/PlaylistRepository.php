<?php
// 📁 src/Repository/PlaylistRepository.php

namespace App\Repository;

use App\Entity\Playlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Playlist> */
class PlaylistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Playlist::class);
    }

    public function search(?string $keyword, ?string $niveau, ?string $categorie): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($keyword) {
            $qb->andWhere('p.title LIKE :kw OR p.description LIKE :kw')
               ->setParameter('kw', '%' . $keyword . '%');
        }

        if ($niveau) {
            $qb->andWhere('p.niveau = :niveau')
               ->setParameter('niveau', $niveau);
        }

        if ($categorie) {
            $qb->andWhere('p.categorie = :categorie')
               ->setParameter('categorie', $categorie);
        }

        // ✅ Tri par niveau (Doctrine-compatible)
        $qb->addOrderBy(
            "CASE
                WHEN p.niveau = 'debutant' THEN 1
                WHEN p.niveau = 'intermediaire' THEN 2
                WHEN p.niveau = 'avance' THEN 3
                ELSE 4
             END",
            'ASC'
        );

        return $qb->getQuery()->getResult();
    }
}
