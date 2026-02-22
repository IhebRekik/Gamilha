<?php
// src/Controller/GameplayController.php

namespace App\Controller;

use App\Entity\Analysis;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/ai')]
class GameplayController extends AbstractController
{
    #[Route('/', name: 'ai_home')]
    public function home(): Response
    {
        return $this->render('gameplay/upload.html.twig');
    }

    #[Route('/upload', name: 'upload', methods: ['POST'])]
    public function upload(
        Request $request,
        EntityManagerInterface $em,
        HttpClientInterface $client
    ): Response {

        $video = $request->files->get('video');

        if (!$video) {
            return $this->render('gameplay/upload.html.twig', [
                'error' => 'Aucun fichier sélectionné !'
            ]);
        }

        $filename = uniqid() . '.mp4';

        // 1️⃣ Dossier vidéos
        $videosDir = $this->getParameter('kernel.project_dir') . '/public/videos';
        if (!is_dir($videosDir)) mkdir($videosDir, 0777, true);

        $video->move($videosDir, $filename);
        $videoPath = $videosDir . '/' . $filename;

        // 2️⃣ Dossier frames
        $framesFolder = $this->getParameter('kernel.project_dir') . '/public/frames/' . pathinfo($filename, PATHINFO_FILENAME);
        if (!is_dir($framesFolder)) mkdir($framesFolder, 0777, true);

        $framesPath = $framesFolder . '/frame_%04d.png';

        // 3️⃣ Extraction frames avec FFmpeg
        $ffmpegPath = 'C:\\ffmpeg\\bin\\ffmpeg.exe'; // adapte si besoin
        $cmd = "\"$ffmpegPath\" -i \"$videoPath\" -vf \"select='gt(scene,0.3)',showinfo\" -vsync vfr \"$framesPath\"";
        exec($cmd);
        // 4️⃣ Récupérer les frames
        $framesFiles = glob($framesFolder . '/frame_*.png');

        // ⚡ Limiter à 4 images pour éviter surcharge mémoire
        $framesFiles = array_slice($framesFiles, 0, 4);

        if (empty($framesFiles)) {
            return new Response("Aucune frame générée.");
        }

        // 5️⃣ Convertir images en base64
        $imagesBase64 = [];
        foreach ($framesFiles as $frame) {
            $imagesBase64[] = base64_encode(file_get_contents($frame));
        }

        // 6️⃣ Créer analyse en base
        $analysis = new Analysis();
        $analysis->setVideoName($filename);
        $analysis->setResult("Analyse en cours...");
        $em->persist($analysis);
        $em->flush();

      try {

    // ===============================
    // 1️⃣ ETAPE 1 : DESCRIPTION NEUTRE
    // ===============================

    $response1 = $client->request('POST', 'http://localhost:11434/api/generate', [
        'json' => [
            'model' => 'llava',
            'prompt' => "
Décris uniquement ce qui est visuellement observable dans ces images de League of Legends.

Ne fais aucune interprétation.
Ne parle pas d’engagement mental.
Ne devine pas l’intention.

Liste :
- Nombre de champions visibles
- Qui semble isolé
- Qui semble avancé
- Si un champion semble en danger
",
            'images' => $imagesBase64,
            'stream' => false
        ],
        'timeout' => 300
    ]);

    $data1 = $response1->toArray();
    $description = $data1['response'] ?? 'Description indisponible';


    
    $data2 = $response1->toArray();
    $analysisText = $data2['response'] ?? 'Erreur lors du coaching';

} catch (\Exception $e) {

    $analysisText = 'Erreur Ollama: ' . $e->getMessage();
}
        // 8️⃣ Sauvegarder résultat
        $analysis->setResult($analysisText);
        $em->flush();

        return $this->redirectToRoute('result', ['id' => $analysis->getId()]);
    }

    #[Route('/result/{id}', name: 'result')]
    public function result(Analysis $analysis): Response
    {
        return $this->render('gameplay/result.html.twig', [
            'analysis' => $analysis
        ]);
    }
}
