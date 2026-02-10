<?php

namespace App\Controller;

use App\Entity\Playlist;
use App\Form\PlaylistType;
use App\Repository\PlaylistRepository;
use App\Repository\CoachingVideoRepository; // ✅ IMPORT CORRECT
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/playlist')]
class PlaylistController extends AbstractController
{
    // 📄 LISTE DES PLAYLISTS + FILTRES
    #[Route('/', name: 'playlist_index')]
    public function index(
        Request $request,
        PlaylistRepository $repository
    ): Response {
        $playlists = $repository->search(
            $request->query->get('q'),
            $request->query->get('niveau'),
            $request->query->get('categorie')
        );

        return $this->render('playlist/index.html.twig', [
            'playlists' => $playlists,
        ]);
    }

    // ➕ AJOUTER UNE PLAYLIST
    #[Route('/new', name: 'playlist_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $playlist = new Playlist();
        $form = $this->createForm(PlaylistType::class, $playlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($playlist);
            $em->flush();

            return $this->redirectToRoute('playlist_index');
        }

        return $this->render('playlist/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // 👀 AFFICHER UNE PLAYLIST + VIDÉOS (AVEC RECHERCHE & FILTRE)
    #[Route('/{id}', name: 'playlist_show', requirements: ['id' => '\d+'])]
    public function show(
        Playlist $playlist,
        Request $request,
        CoachingVideoRepository $videoRepository
    ): Response {
        $videos = $videoRepository->searchInPlaylist(
            $playlist->getId(),
            $request->query->get('q'),
            $request->query->get('niveau')
        );

        return $this->render('playlist/show.html.twig', [
            'playlist' => $playlist,
            'videos' => $videos,
        ]);
    }

    // ✏️ MODIFIER UNE PLAYLIST
    #[Route('/{id}/edit', name: 'playlist_edit', requirements: ['id' => '\d+'])]
    public function edit(
        Playlist $playlist,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(PlaylistType::class, $playlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('playlist_index');
        }

        return $this->render('playlist/edit.html.twig', [
            'form' => $form->createView(),
            'playlist' => $playlist,
        ]);
    }

    // ❌ SUPPRIMER UNE PLAYLIST
    #[Route('/{id}/delete', name: 'playlist_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(
        Playlist $playlist,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $playlist->getId(),
            $request->request->get('_token')
        )) {
            $em->remove($playlist);
            $em->flush();
        }

        return $this->redirectToRoute('playlist_index');
    }
}
