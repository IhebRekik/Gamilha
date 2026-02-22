<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\StreamPredictionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

#[Route('/api/prediction')]
class PredictionController extends AbstractController
{
    private StreamPredictionService $predictionService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        StreamPredictionService $predictionService,
        EntityManagerInterface $entityManager
    ) {
        $this->predictionService = $predictionService;
        $this->entityManager = $entityManager;
    }

    /**
     * Prédit le nombre de streams pour l'utilisateur connecté
     * 
     * @OA\Get(
     *     path="/api/prediction/streams",
     *     summary="Prédire le nombre de streams",
     *     description="Retourne une prédiction du nombre de streams pour l'utilisateur connecté sur une période donnée",
     *     @OA\Parameter(
     *         name="days_ahead",
     *         in="query",
     *         description="Nombre de jours pour la prédiction",
     *         @OA\Schema(type="integer", default=30)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prédiction réussie",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="prediction", type="object"),
     *             @OA\Property(property="contributions", type="object"),
     *             @OA\Property(property="recommendations", type="array")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    #[Route('/streams', name: 'api_prediction_streams', methods: ['GET'])]
    public function predictStreams(Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json([
                'error' => 'Utilisateur non authentifié',
                'message' => 'Vous devez être connecté pour accéder à cette fonctionnalité'
            ], 401);
        }

        $daysAhead = $request->query->getInt('days_ahead', 30);
        
        // Valider le paramètre days_ahead
        if ($daysAhead < 1 || $daysAhead > 365) {
            return $this->json([
                'error' => 'Paramètre invalide',
                'message' => 'Le paramètre days_ahead doit être entre 1 et 365'
            ], 400);
        }

        try {
            $prediction = $this->predictionService->predictStreams($user, $daysAhead);
            
            return $this->json($prediction);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur de prédiction',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prédit le nombre de streams pour un utilisateur spécifique (admin)
     * 
     * @OA\Get(
     *     path="/api/prediction/streams/{userId}",
     *     summary="Prédire les streams d'un utilisateur",
     *     description="Retourne une prédiction du nombre de streams pour un utilisateur spécifique",
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="days_ahead",
     *         in="query",
     *         description="Nombre de jours pour la prédiction",
     *         @OA\Schema(type="integer", default=30)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prédiction réussie",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Accès interdit"),
     *     @OA\Response(response=404, description="Utilisateur non trouvé")
     * )
     */
    #[Route('/streams/{userId}', name: 'api_prediction_streams_user', methods: ['GET'])]
    public function predictStreamsForUser(int $userId, Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur est un admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($userId);

        if (!$user) {
            return $this->json([
                'error' => 'Utilisateur non trouvé',
                'message' => 'Aucun utilisateur trouvé avec l\'ID ' . $userId
            ], 404);
        }

        $daysAhead = $request->query->getInt('days_ahead', 30);
        
        // Valider le paramètre days_ahead
        if ($daysAhead < 1 || $daysAhead > 365) {
            return $this->json([
                'error' => 'Paramètre invalide',
                'message' => 'Le paramètre days_ahead doit être entre 1 et 365'
            ], 400);
        }

        try {
            $prediction = $this->predictionService->predictStreams($user, $daysAhead);
            
            return $this->json($prediction);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur de prédiction',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les contributions d'un streamer
     * 
     * @OA\Get(
     *     path="/api/prediction/contributions",
     *     summary="Obtenir les contributions",
     *     description="Retourne les statistiques et contributions de l'utilisateur connecté",
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         description="Nombre de jours d'historique",
     *         @OA\Schema(type="integer", default=90)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    #[Route('/contributions', name: 'api_prediction_contributions', methods: ['GET'])]
    public function getContributions(Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json([
                'error' => 'Utilisateur non authentifié',
                'message' => 'Vous devez être connecté pour accéder à cette fonctionnalité'
            ], 401);
        }

        $days = $request->query->getInt('days', 90);
        
        // Valider le paramètre days
        if ($days < 7 || $days > 365) {
            return $this->json([
                'error' => 'Paramètre invalide',
                'message' => 'Le paramètre days doit être entre 7 et 365'
            ], 400);
        }

        try {
            $contributions = $this->predictionService->getStreamerContributions($user, $days);
            
            return $this->json($contributions);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prédiction avec AI (optionnel - nécessite clé API)
     * 
     * @OA\Get(
     *     path="/api/prediction/ai",
     *     summary="Prédiction AI",
     *     description="Retourne une prédiction utilisant l'API AI externe",
     *     @OA\Parameter(
     *         name="days_ahead",
     *         in="query",
     *         description="Nombre de jours pour la prédiction",
     *         @OA\Schema(type="integer", default=30)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prédiction AI réussie",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    #[Route('/ai', name: 'api_prediction_ai', methods: ['GET'])]
    public function predictWithAI(Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json([
                'error' => 'Utilisateur non authentifié',
                'message' => 'Vous devez être connecté pour accéder à cette fonctionnalité'
            ], 401);
        }

        $daysAhead = $request->query->getInt('days_ahead', 30);
        
        // Valider le paramètre days_ahead
        if ($daysAhead < 1 || $daysAhead > 365) {
            return $this->json([
                'error' => 'Paramètre invalide',
                'message' => 'Le paramètre days_ahead doit être entre 1 et 365'
            ], 400);
        }

        try {
            $prediction = $this->predictionService->predictWithAI($user, $daysAhead);
            
            return $this->json($prediction);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur de prédiction AI',
                'message' => 'La prédiction AI n\'est pas disponible. Utilisation de l\'algorithme local.',
                'fallback' => true
            ], 200);
        }
    }
}
