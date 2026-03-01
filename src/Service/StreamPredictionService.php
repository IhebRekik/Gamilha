<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Stream;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class StreamPredictionService
{
    private ?string $aiApiKey;
private string $aiApiUrl;

public function __construct(
    #[Autowire('%ai.api_key%')] ?string $aiApiKey = null,
    #[Autowire('%ai.api_url%')] ?string $aiApiUrl = null
) {
    $this->aiApiKey = $aiApiKey;
    $this->aiApiUrl = $aiApiUrl ?? 'https://api.openai.com/v1/chat/completions';
}

    /**
     * Collecte les contributions d'un streamer pour l'analyse
     */
    public function getStreamerContributions(User $user, int $daysHistory = 90): array
    {
        $streams = $user->getStreams();
        $donations = $user->getDonations();
        
        $cutoffDate = new \DateTime("-{$daysHistory} days");
        
        // Filtrer les streams récents
        $recentStreams = $streams->filter(function(Stream $stream) use ($cutoffDate) {
            return $stream->getCreatedAt() >= $cutoffDate;
        });

        // Filtrer les donations récentes
        $recentDonations = $donations->filter(function($donation) use ($cutoffDate) {
            return $donation->getCreatedAt() >= $cutoffDate;
        });

        // Calculer les statistiques
        $totalStreams = count($recentStreams);
        $totalViewers = 0;
        $totalDonations = 0;
        $donationAmount = 0.0;

        foreach ($recentStreams as $stream) {
            $totalViewers += $stream->getViewers();
        }

        foreach ($recentDonations as $donation) {
            $totalDonations++;
            $donationAmount += $donation->getAmount();
        }

        // Calculer la fréquence de streaming (streams par semaine)
        $weeks = max(1, $daysHistory / 7);
        $streamsPerWeek = $totalStreams / $weeks;
        
        // Calculer les viewers moyens
        $avgViewers = $totalStreams > 0 ? $totalViewers / $totalStreams : 0;

        // Calculer la durée depuis le dernier stream
        $lastStreamDate = null;
        if (count($recentStreams) > 0) {
            $lastStreamDate = $recentStreams->first()->getCreatedAt();
            foreach ($recentStreams as $stream) {
                if ($stream->getCreatedAt() > $lastStreamDate) {
                    $lastStreamDate = $stream->getCreatedAt();
                }
            }
        }

        $daysSinceLastStream = null;
        if ($lastStreamDate) {
            $daysSinceLastStream = (new \DateTime())->diff($lastStreamDate)->days;
        }

        return [
            'user_id' => $user->getId(),
            'user_name' => $user->getName(),
            'period_days' => $daysHistory,
            'total_streams' => $totalStreams,
            'streams_per_week' => round($streamsPerWeek, 2),
            'total_viewers' => $totalViewers,
            'avg_viewers_per_stream' => round($avgViewers, 2),
            'total_donations' => $totalDonations,
            'total_donation_amount' => round($donationAmount, 2),
            'avg_donation_per_stream' => $totalStreams > 0 ? round($donationAmount / $totalStreams, 2) : 0,
            'days_since_last_stream' => $daysSinceLastStream,
            'last_stream_date' => $lastStreamDate ? $lastStreamDate->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Prédit le nombre de streams pour une période donnée
     */
    public function predictStreams(User $user, int $daysAhead = 30): array
    {
        // Obtenir les contributions des 90 derniers jours
        $contributions = $this->getStreamerContributions($user, 90);
        
        // Calculer la prédiction basée sur les tendances
        $basePrediction = $this->calculateBasePrediction($contributions, $daysAhead);
        
        // Appliquer un facteur de confiance
        $confidence = $this->calculateConfidence($contributions);
        
        // Générer des recommandations
        $recommendations = $this->generateRecommendations($contributions, $daysAhead);

        return [
            'prediction' => [
                'streams_expected' => round($basePrediction),
                'streams_min' => round($basePrediction * (1 - (1 - $confidence) * 0.5)),
                'streams_max' => round($basePrediction * (1 + (1 - $confidence) * 0.5)),
                'period_days' => $daysAhead,
                'confidence_level' => $confidence,
                'confidence_text' => $this->getConfidenceText($confidence),
            ],
            'contributions' => $contributions,
            'recommendations' => $recommendations,
            'model_info' => [
                'version' => '1.0.0',
                'type' => 'statistical_prediction',
                'last_updated' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]
        ];
    }

    /**
     * Calcule la prédiction de base basée sur les statistiques
     */
    private function calculateBasePrediction(array $contributions, int $daysAhead): float
    {
        $streamsPerWeek = $contributions['streams_per_week'];
        $weeksAhead = $daysAhead / 7;
        
        $basePrediction = $streamsPerWeek * $weeksAhead;

        // Facteur d'ajustement basé sur l'activité récente
        $daysSinceLastStream = $contributions['days_since_last_stream'];
        
        if ($daysSinceLastStream !== null) {
            // Si le streamer n'a pas streamé récemment, réduire la prédiction
            if ($daysSinceLastStream > 30) {
                $basePrediction *= 0.7;
            } elseif ($daysSinceLastStream > 14) {
                $basePrediction *= 0.85;
            } elseif ($daysSinceLastStream < 3) {
                // S'il a streamé récemment, augmenter légèrement la prédiction
                $basePrediction *= 1.1;
            }
        }

        // Ajuster basé sur les donations (indice d'engagement)
        if ($contributions['total_donations'] > 10) {
            $basePrediction *= 1.15;
        } elseif ($contributions['total_donations'] === 0 && $contributions['total_streams'] > 0) {
            $basePrediction *= 0.9;
        }

        return max(0, $basePrediction);
    }

    /**
     * Calcule le niveau de confiance de la prédiction
     */
    private function calculateConfidence(array $contributions): float
    {
        $totalStreams = $contributions['total_streams'];
        
        // Plus il y a de données historiques, plus la confiance est élevée
        if ($totalStreams >= 20) {
            return 0.85;
        } elseif ($totalStreams >= 10) {
            return 0.70;
        } elseif ($totalStreams >= 5) {
            return 0.55;
        } elseif ($totalStreams >= 1) {
            return 0.40;
        }
        
        return 0.20; // Pas assez de données
    }

    /**
     * Génère du texte pour le niveau de confiance
     */
    private function getConfidenceText(float $confidence): string
    {
        if ($confidence >= 0.8) {
            return 'Très haute';
        } elseif ($confidence >= 0.6) {
            return 'Haute';
        } elseif ($confidence >= 0.4) {
            return 'Moyenne';
        } elseif ($confidence >= 0.2) {
            return 'Basse';
        }
        return 'Très basse';
    }

    /**
     * Génère des recommandations pour améliorer les performances
     */
    private function generateRecommendations(array $contributions, int $daysAhead): array
    {
        $recommendations = [];

        $streamsPerWeek = $contributions['streams_per_week'] ?? 0;
        $totalDonations = $contributions['total_donations'] ?? 0;
        $daysSinceLastStream = $contributions['days_since_last_stream'];
        $avgViewers = $contributions['avg_viewers_per_stream'] ?? 0;

        // Recommandation sur la fréquence
        if ($streamsPerWeek < 1) {
            $recommendations[] = [
                'type' => 'frequency',
                'priority' => 'high',
                'message' => 'Augmentez votre fréquence de streaming à au moins 1 stream par semaine pour grow votre audience.',
            ];
        } elseif ($streamsPerWeek >= 3) {
            $recommendations[] = [
                'type' => 'frequency',
                'priority' => 'medium',
                'message' => 'Excellente fréquence de streaming ! Maintenez ce rythme.',
            ];
        }

        // Recommandation sur les donations
        if ($totalDonations === 0 && $contributions['total_streams'] > 0) {
            $recommendations[] = [
                'type' => 'engagement',
                'priority' => 'high',
                'message' => 'Travaillez sur l\'engagement de votre communauté pour générer des donations.',
            ];
        }

        // Recommandation sur l'activité récente
        if ($daysSinceLastStream !== null && $daysSinceLastStream > 14) {
            $recommendations[] = [
                'type' => 'consistency',
                'priority' => 'high',
                'message' => 'Revenez streamer régulièrement pour maintenir votre audience.',
            ];
        }

        // Recommandation sur les viewers
        if ($avgViewers < 10 && $contributions['total_streams'] > 0) {
            $recommendations[] = [
                'type' => 'viewers',
                'priority' => 'medium',
                'message' => 'Travaillez sur la promotion de vos streams pour augmenter votre audience.',
            ];
        }

        return $recommendations;
    }

    /**
     * Appelle une API AI externe pour une prédiction plus avancée (optionnel)
     */
    public function predictWithAI(User $user, int $daysAhead = 30): array
    {
        if (!$this->aiApiKey) {
            // Fallback vers l'algorithme local
            return $this->predictStreams($user, $daysAhead);
        }

        $contributions = $this->getStreamerContributions($user, 90);
        
        $prompt = $this->buildAIPrompt($contributions, $daysAhead);
        
        try {
            $response = $this->callAIApi($prompt);
            return $this->parseAIResponse($response, $contributions, $daysAhead);
        } catch (\Exception $e) {
            // Fallback en cas d'erreur
            return $this->predictStreams($user, $daysAhead);
        }
    }

    /**
     * Construit le prompt pour l'API AI
     */
    private function buildAIPrompt(array $contributions, int $daysAhead): string
    {
        return sprintf(
            "Tu es un expert en analyse de données de streaming. Basé sur les statistiques suivantes d'un streamer:\n\n%s\n\nPrédit le nombre de streams qu'il va faire dans les %d prochains jours. Réponds en JSON avec: predicted_streams (nombre), confidence (0-1), et recommendations (tableau de conseils).",
            json_encode($contributions, JSON_PRETTY_PRINT),
            $daysAhead
        );
    }

    /**
     * Appelle l'API AI externe
     */
    private function callAIApi(string $prompt): array
    {
        $client = new \Symfony\Component\HttpClient\NativeHttpClient();
        
        $response = $client->request('POST', $this->aiApiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
            ]
        ]);

        return json_decode($response->getContent(), true);
    }

    /**
     * Parse la réponse de l'API AI
     */
    private function parseAIResponse(array $response, array $contributions, int $daysAhead): array
    {
        // Extraire et parser le contenu JSON de la réponse
        $content = $response['choices'][0]['message']['content'] ?? '{}';
        $aiPrediction = json_decode($content, true) ?? [];

        return [
            'prediction' => [
                'streams_expected' => $aiPrediction['predicted_streams'] ?? 0,
                'streams_min' => $aiPrediction['predicted_streams'] ?? 0,
                'streams_max' => $aiPrediction['predicted_streams'] ?? 0,
                'period_days' => $daysAhead,
                'confidence_level' => $aiPrediction['confidence'] ?? 0.5,
                'confidence_text' => 'AI Generated',
            ],
            'contributions' => $contributions,
            'recommendations' => $aiPrediction['recommendations'] ?? [],
            'model_info' => [
                'version' => '1.0.0',
                'type' => 'ai_enhanced_prediction',
                'ai_model' => 'gpt-3.5-turbo',
                'last_updated' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]
        ];
    }
}
