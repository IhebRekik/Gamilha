<?php

namespace App\Repository;

use App\Entity\Evenement;
use App\Entity\User;
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

    /**
     * @return Evenement[]
     */
    public function findByCreatedBy(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.createdBy = :user')
            ->setParameter('user', $user)
            ->orderBy('e.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les événements où les équipes du user participent.
     *
     * @return Evenement[]
     */
    public function findByUserParticipations(User $user): array
    {
        $equipes = $user->getEquipes();
        if ($equipes->isEmpty()) {
            return [];
        }

        return $this->createQueryBuilder('e')
            ->innerJoin('e.equipesParticipantes', 'eq')
            ->where('eq IN (:equipes)')
            ->setParameter('equipes', $equipes)
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les événements similaires basés sur la description et le jeu.
     * La similarité est calculée en recherchant des mots-clés communs dans la description
     * et en priorisant les événements du même jeu.
     *
     * @return Evenement[]
     */
    public function findSimilarEvents(Evenement $evenement, int $limit = 4): array
    {
        $description = $evenement->getDescription();
        $currentId = $evenement->getIdEvenement();
        
        // Si pas de description, pas de similarité possible
        if (empty($description)) {
            return [];
        }

        // Extraire les mots-clés de la description (mots de plus de 3 caractères)
        $words = preg_split('/\s+/', strtolower($description));
        $keywords = array_filter($words, function($word) {
            return strlen($word) > 3 && !in_array($word, ['avec', 'pour', 'dans', 'cette', 'être', 'avoir', 'plus', 'tous', 'toute', 'comme', 'leurs']);
        });

        // Si pas assez de mots-clés, pas de similarité
        if (count($keywords) < 2) {
            return [];
        }

        // Prendre les 5 premiers mots-clés
        $keywords = array_slice(array_values($keywords), 0, 5);

        // Récupérer tous les événements sauf l'actuel
        $allEvents = $this->createQueryBuilder('e')
            ->andWhere('e.idEvenement != :currentId')
            ->setParameter('currentId', $currentId)
            ->orderBy('e.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();

        if (empty($allEvents)) {
            return [];
        }

        // Fonction pour calculer le score de similarité
        $calculateScore = function($event) use ($keywords, $evenement) {
            $score = 0;
            $eventDesc = strtolower($event->getDescription() ?? '');
            
            // Priorité 1: même jeu (score +10)
            if ($event->getJeu() === $evenement->getJeu()) {
                $score += 10;
            }
            
            // Priorité 2: mots-clés communs dans la description (score +3 par mot)
            foreach ($keywords as $keyword) {
                if (strpos($eventDesc, $keyword) !== false) {
                    $score += 3;
                }
            }
            
            return $score;
        };

        // Calculer le score pour chaque événement
        $scoredEvents = [];
        foreach ($allEvents as $event) {
            $score = $calculateScore($event);
            if ($score > 0) {
                $scoredEvents[] = ['event' => $event, 'score' => $score];
            }
        }

        // Trier par score décroissant
        usort($scoredEvents, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        // Prendre les événements avec score > 0
        $results = array_map(function($item) {
            return $item['event'];
        }, $scoredEvents);

        return array_slice($results, 0, $limit);
    }
        public function countByGame(): array
{
    return $this->createQueryBuilder('t')
        ->select('t.jeu as jeu')
        ->addSelect('COUNT(t) as total')
        ->groupBy('t.jeu')
        ->orderBy('total', 'DESC')
        ->getQuery()
        ->getResult();
}
}
