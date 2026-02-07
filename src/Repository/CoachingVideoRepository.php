<?php

namespace App\Repository;

use App\Entity\CoachingVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoachingVideo>
 */
class CoachingVideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoachingVideo::class);
    }
   



    public function searchInPlaylist(
    int $playlistId,
    ?string $keyword,
    ?string $niveau
): array {
    $qb = $this->createQueryBuilder('v')
        ->andWhere('v.playlist = :playlist')
        ->setParameter('playlist', $playlistId);

    if ($keyword) {
        $qb->andWhere('v.titre LIKE :kw OR v.description LIKE :kw')
           ->setParameter('kw', '%' . $keyword . '%');
    }

    if ($niveau) {
        $qb->andWhere('v.niveau = :niveau')
           ->setParameter('niveau', $niveau);
    }

    // 🔃 ordre logique des niveaux
    $qb->addOrderBy(
        "CASE 
            WHEN v.niveau = 'debutant' THEN 1
            WHEN v.niveau = 'intermediaire' THEN 2
            WHEN v.niveau = 'avance' THEN 3
            ELSE 4
         END"
    );

    return $qb->getQuery()->getResult();
}



    // 🔹 CRUD BASIQUE UNIQUEMENT
    // find() / findAll() / findBy() / findOneBy()
    // sont déjà fournis par ServiceEntityRepository
}
