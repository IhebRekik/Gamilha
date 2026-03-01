<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
    
    public function findByContent(string $content): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.content LIKE :content')
            ->setParameter('content', '%' . $content . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserEmail(string $email): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->where('u.email LIKE :email')
            ->setParameter('email', '%' . $email . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }


}