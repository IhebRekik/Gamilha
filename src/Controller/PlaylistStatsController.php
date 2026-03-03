<?php
// 📁 src/Controller/PlaylistStatsController.php

namespace App\Controller;

use App\Entity\Playlist;
use App\Repository\CoachingVideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/stats')]
class PlaylistStatsController extends AbstractController
{
    /**
     * Retourne les statistiques d'une playlist pour l'utilisateur.
     * Appelé en AJAX depuis showFront.html.twig
     *
     * GET /api/stats/playlist/{id}?watched[]=1&watched[]=3
     */
    #[Route('/playlist/{id}', name: 'stats_playlist', methods: ['GET'])]
    public function playlistStats(
        Playlist $playlist,
        Request $request,
        CoachingVideoRepository $videoRepo
    ): JsonResponse {
        $videos = $videoRepo->searchInPlaylist($playlist->getId(), null, null);

        // IDs des vidéos vues (stockés en localStorage côté client, envoyés via query string)
        // ✅ Compatible avec watched[0]=1&watched[1]=2 ET watched[]=1&watched[]=2
        $rawWatched = $request->query->all('watched');
        if (!is_array($rawWatched)) {
            $rawWatched = [];
        }
        $watchedIds = array_values(array_map('intval', $rawWatched));

        $totalVideos     = count($videos);
        $premiumCount    = 0;
        $freeCount       = 0;
        $niveauCount     = ['debutant' => 0, 'intermediaire' => 0, 'avance' => 0];
        $watchedCount    = 0;
        $totalDuration   = 0;
        $watchedDuration = 0;

        foreach ($videos as $video) {
            // Comptage premium / gratuit
            if ($video->isPremium()) {
                $premiumCount++;
            } else {
                $freeCount++;
            }

            // Comptage par niveau
            $niv = $video->getNiveau();
            $niveauCount[$niv] = ($niveauCount[$niv] ?? 0) + 1;

            // Durée (champ real ou estimation 10min)
            $dur = $video->getDuration() ?? 600;
            $totalDuration += $dur;

            // Vidéos vues
            if (in_array($video->getId(), $watchedIds)) {
                $watchedCount++;
                $watchedDuration += $dur;
            }
        }

        $progressPct    = $totalVideos > 0 ? round(($watchedCount / $totalVideos) * 100) : 0;
        $dominantNiveau = count($niveauCount) > 0 ? array_search(max($niveauCount), $niveauCount) : null;

        return $this->json([
            'totalVideos'       => $totalVideos,
            'premiumCount'      => $premiumCount,
            'freeCount'         => $freeCount,
            'niveauRepartition' => $niveauCount,
            'dominantNiveau'    => $dominantNiveau,
            'watchedCount'      => $watchedCount,
            'watchedDuration'   => $watchedDuration,
            'totalDuration'     => $totalDuration,
            'progressPercent'   => $progressPct,
            'formattedTotal'    => $this->formatDuration($totalDuration),
            'formattedWatched'  => $this->formatDuration($watchedDuration),
        ]);
    }

    private function formatDuration(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        if ($h > 0) {
            return sprintf('%dh %02dmin', $h, $m);
        }
        return sprintf('%dmin %02ds', $m, $s);
    }
}
