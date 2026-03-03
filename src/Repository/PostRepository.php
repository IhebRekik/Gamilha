<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use Doctrine\Common\Collections\Criteria;

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
            ->innerJoin('p.user', 'u')
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

    /**
     * Optimized smart feed using multi-step hydration
     */
    public function findSmartFeed(User $currentUser): array
    {
        $em = $this->getEntityManager();

        // Step 1: Load posts + author
        $posts = $em->createQuery(
            'SELECT p, author
             FROM App\Entity\Post p
             INNER JOIN p.user author
             ORDER BY p.createdAt DESC'
        )->getResult();

        // Step 2: Load likedBy for all posts
        $postIds = array_map(fn($p) => $p->getId(), $posts);
        if ($postIds) {
            $em->createQuery(
                'SELECT PARTIAL p.{id}, l
                 FROM App\Entity\Post p
                 LEFT JOIN p.likedBy l
                 WHERE p.id IN (:ids)'
            )->setParameter('ids', $postIds)
             ->getResult(); // Hydration side-effect
        }

        // Step 3: Load commentaires for all posts
        if ($postIds) {
            $em->createQuery(
                'SELECT PARTIAL p.{id}, c
                 FROM App\Entity\Post p
                 LEFT JOIN p.commentaires c
                 WHERE p.id IN (:ids)'
            )->setParameter('ids', $postIds)
             ->getResult();
        }

        // Step 4: Load Friend relations (INNER JOIN because FK NOT NULL)
        $em->createQuery(
            'SELECT f
             FROM App\Entity\Friend f
             WHERE f.user = :currentUser AND f.friend IN (:authors)'
        )->setParameter('currentUser', $currentUser)
          ->setParameter('authors', array_map(fn($p) => $p->getUser(), $posts))
          ->getResult();

        // Step 5: Compute score in PHP
        $scoredPosts = [];
        foreach ($posts as $post) {
            $likesCount = $post->getLikedBy()->count();
            $commentsCount = $post->getCommentaires()->count();
            $isFriend = $currentUser->getFriends()->contains($post->getUser()) ? 1 : 0;

            $score = $isFriend * 100 + $likesCount * 2 + $commentsCount * 3;
            $scoredPosts[] = ['post' => $post, 'score' => $score];
        }

        // Step 6: Sort by score DESC, then createdAt DESC
        usort($scoredPosts, fn($a, $b) => $b['score'] <=> $a['score'] ?: $b['post']->getCreatedAt() <=> $a['post']->getCreatedAt());

        // Return posts only
        return array_map(fn($p) => $p['post'], $scoredPosts);
    }
}