<?php
// 📁 src/Controller/SubtitleController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api/subtitle')]
class SubtitleController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $groqApiKey
    ) {}

    /**
     * Génère les sous-titres d'un fichier vidéo local via Groq Whisper large-v3.
     * GET /api/subtitle/generate?videoPath=uploads/videos/xxx.mp4&lang=auto
     */
    #[Route('/generate', name: 'subtitle_generate', methods: ['GET'])]
    public function generate(Request $request): JsonResponse
    {
        $videoPath = $request->query->get('videoPath');
        // lang=auto (défaut) ou forcer : fr, ar, en, es...
        $lang = $request->query->get('lang', 'auto');

        if (!$videoPath) {
            return $this->json(['error' => 'Paramètre videoPath manquant'], 400);
        }

        $fullPath = $this->getParameter('kernel.project_dir') . '/public/' . ltrim($videoPath, '/');

        if (!file_exists($fullPath)) {
            return $this->json(['error' => 'Fichier vidéo introuvable : ' . $videoPath], 404);
        }

        try {
            // ✅ Construction du body multipart
            $body = [
                'file'            => fopen($fullPath, 'r'),
                'model'           => 'whisper-large-v3',
                'response_format' => 'verbose_json',  // retourne segments + timestamps
                'temperature'     => '0',             // résultats plus stables
            ];

            // ✅ Langue : auto-détection si 'auto', sinon forcer la langue
            if ($lang !== 'auto') {
                $body['language'] = $lang;
            }

            $response = $this->httpClient->request(
                'POST',
                'https://api.groq.com/openai/v1/audio/transcriptions',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->groqApiKey,
                    ],
                    'body'    => $body,
                    'timeout' => 60,
                ]
            );

            $data = $response->toArray();

            // ✅ Construire les sous-titres avec timestamps propres
            $subtitles = [];
            foreach (($data['segments'] ?? []) as $seg) {
                $text = trim($seg['text'] ?? '');
                if ($text === '') continue;

                $subtitles[] = [
                    'start' => round((float)$seg['start'], 2),
                    'end'   => round((float)$seg['end'],   2),
                    'text'  => $text,
                ];
            }

            return $this->json([
                'success'           => true,
                'detectedLanguage'  => $data['language'] ?? 'unknown',
                'text'              => $data['text'] ?? '',
                'subtitles'         => $subtitles,
                'segmentsCount'     => count($subtitles),
            ]);

        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error'   => 'Erreur API Groq : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pour les vidéos YouTube — retourne l'ID pour activer les CC natifs.
     * GET /api/subtitle/youtube?url=https://www.youtube.com/embed/XXXX
     */
    #[Route('/youtube', name: 'subtitle_youtube', methods: ['GET'])]
    public function youtube(Request $request): JsonResponse
    {
        $url = $request->query->get('url', '');

        if (preg_match('/(?:youtube\.com\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $m)) {
            return $this->json([
                'success'   => true,
                'youtubeId' => $m[1],
            ]);
        }

        return $this->json(['success' => false, 'message' => 'URL YouTube non reconnue']);
    }
}
