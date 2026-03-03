<?php
// 📁 src/Controller/CoachingVideoController.php

namespace App\Controller;

use App\Entity\CoachingVideo;
use App\Form\CoachingVideoType;
use App\Repository\CoachingVideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/video')]
class CoachingVideoController extends AbstractController
{
    #[Route('/', name: 'video_index')]
    public function index(CoachingVideoRepository $repository): Response
    {
        return $this->render('coachingvideo/index.html.twig', [
            'videos' => $repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'video_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $video = new CoachingVideo();
        $form  = $this->createForm(CoachingVideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $uploadedFile = $form->get('videoFile')->getData();

            if ($uploadedFile) {
                $fileName = uniqid() . '.' . $uploadedFile->guessExtension();
                $uploadedFile->move($this->getParameter('videos_directory'), $fileName);
                $video->setUrl('uploads/videos/' . $fileName);
            } elseif ($video->getUrl()) {
                // ✅ Convertir automatiquement YouTube watch -> embed
                $video->setUrl($this->convertToEmbedUrl($video->getUrl()));
            }

            $em->persist($video);
            $em->flush();

            $this->addFlash('video_added', json_encode([
                'titre'      => $video->getTitre(),
                'niveau'     => $video->getNiveau(),
                'premium'    => $video->isPremium(),
                'playlistId' => $video->getPlaylist()?->getId(),
            ]));

            return $this->redirectToRoute('playlist_show', [
                'id' => $video->getPlaylist()?->getId(),
            ]);
        }

        return $this->render('coachingvideo/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'video_edit')]
    public function edit(CoachingVideo $video, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CoachingVideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $uploadedFile = $form->get('videoFile')->getData();

            if ($uploadedFile) {
                $fileName = uniqid() . '.' . $uploadedFile->guessExtension();
                $uploadedFile->move($this->getParameter('videos_directory'), $fileName);
                $video->setUrl('uploads/videos/' . $fileName);
            } elseif ($video->getUrl()) {
                // ✅ Convertir aussi à la modification
                $video->setUrl($this->convertToEmbedUrl($video->getUrl()));
            }

            $em->flush();
            $this->addFlash('success', 'Vidéo modifiée avec succès !');

            return $this->redirectToRoute('playlist_show', [
                'id' => $video->getPlaylist()?->getId(),
            ]);
        }

        return $this->render('coachingvideo/edit.html.twig', [
            'form'  => $form->createView(),
            'video' => $video,
        ]);
    }

    #[Route('/{id}/delete', name: 'video_delete', methods: ['POST'])]
    public function delete(CoachingVideo $video, Request $request, EntityManagerInterface $em): Response
    {
        $playlistId = $video->getPlaylist()?->getId();

        if ($this->isCsrfTokenValid('delete' . $video->getId(), $request->request->get('_token'))) {
            $em->remove($video);
            $em->flush();
            $this->addFlash('success', 'Vidéo supprimée avec succès !');
        }

        return $this->redirectToRoute('playlist_show', ['id' => $playlistId]);
    }

    /**
     * ✅ Convertit n'importe quelle URL YouTube en format embed
     * Supporte : watch?v=, youtu.be/, /shorts/, /embed/ (unchanged)
     */
    private function convertToEmbedUrl(string $url): string
    {
        // Déjà embed → retourner tel quel
        if (str_contains($url, 'youtube.com/embed/')) {
            return $url;
        }

        $videoId = null;

        // Format watch?v=ID ou watch?v=ID&list=...
        if (preg_match('/[?&]v=([a-zA-Z0-9_-]{11})/', $url, $m)) {
            $videoId = $m[1];
        }
        // Format youtu.be/ID
        elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
            $videoId = $m[1];
        }
        // Format /shorts/ID
        elseif (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
            $videoId = $m[1];
        }

        if ($videoId) {
            return 'https://www.youtube.com/embed/' . $videoId;
        }

        return $url;
    }
}
