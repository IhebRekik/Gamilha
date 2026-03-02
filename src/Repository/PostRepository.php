<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }
   public function search(?string $term, ?string $user = null): array
{
    $qb = $this->createQueryBuilder('p')
        ->leftJoin('p.user', 'u') // pour accéder aux infos de l'utilisateur
        ->addSelect('u')
        ->orderBy('p.createdAt', 'DESC');

    if ($term) {
        $qb->andWhere('p.content LIKE :term')
           ->setParameter('term', '%' . $term . '%');
    }

    if ($user) {
        $qb->andWhere('u.name LIKE :user')
           ->setParameter('user', '%' . $user . '%');
    }

    return $qb->getQuery()->getResult();
}
public function findSmartFeed(User $currentUser): array
{
    return $this->createQueryBuilder('p')
        ->leftJoin('p.user', 'author')

        // Likes & commentaires
        ->leftJoin('p.likedBy', 'l')
        ->leftJoin('p.commentaires', 'c')

        // Vérifier si l’auteur est un ami
        ->leftJoin(
            'App\Entity\Friend',
            'f',
            'WITH',
            'f.friend = author AND f.user = :currentUser'
        )

        ->addSelect('COUNT(DISTINCT l.id) AS HIDDEN likesCount')
        ->addSelect('COUNT(DISTINCT c.id) AS HIDDEN commentsCount')
        ->addSelect('COUNT(DISTINCT f.id) AS HIDDEN isFriend')

        // Priorité amis > likes > commentaires
        ->addSelect(
            '(COUNT(DISTINCT f.id) * 100
            + COUNT(DISTINCT l.id) * 2
            + COUNT(DISTINCT c.id) * 3
            ) AS HIDDEN score'
        )

        ->setParameter('currentUser', $currentUser)

        ->groupBy('p.id')
        ->orderBy('score', 'DESC')
        ->addOrderBy('p.createdAt', 'DESC')

        ->getQuery()
        ->getResult();
}


}