<?php
// 📁 src/Controller/PlaylistController.php

namespace App\Controller;

use App\Entity\Playlist;
use App\Form\PlaylistType;
use App\Repository\PlaylistRepository;
use App\Repository\CoachingVideoRepository;
use App\Repository\UserAbonnementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/playlist')]
class PlaylistController extends AbstractController
{
    // 📄 LISTE DES PLAYLISTS (admin)
    #[Route('/', name: 'playlist_index')]
    public function index(Request $request, PlaylistRepository $repository): Response
    {
        $playlists = $repository->search(
            $request->query->get('q'),
            $request->query->get('niveau'),
            $request->query->get('categorie')
        );

        return $this->render('playlist/index.html.twig', [
            'playlists' => $playlists,
        ]);
    }

    // 📄 LISTE DES PLAYLISTS (front)
    #[Route('/Front', name: 'playlistFront_index')]
    public function indexFront(Request $request, PlaylistRepository $repository , UserRepository $userRepository, UserAbonnementRepository $userAbonnementRepository): Response
    {
        $user = $userRepository->findOneBy(['email' => $request->cookies->get('user_email')]); // Récupérer un utilisateur (ex: ID 1)

            $abonnementsActifs = $userAbonnementRepository->createQueryBuilder('ua')
                ->where('ua.user = :user')
                ->andWhere('ua.dateFin > :now')
                ->setParameter('user', $user)
                ->setParameter('now', new \DateTime())
                ->getQuery()
                ->getResult();
           $hasStreaming = false;

            foreach ($abonnementsActifs as $userAbonnement) {
                $options = $userAbonnement->getAbonnement()->getOptions();

                if ($options && in_array('coching', $options)) {
                    $hasStreaming = true;
                    break; // inutile de continuer
                }
            }

            if (!$hasStreaming) {
                $this->addFlash('danger', 'Votre abonnement ne permet pas le streaming.');
                return $this->redirectToRoute('app_abonnementuser_index');
            }

        $playlists = $repository->search(
            $request->query->get('q'),
            $request->query->get('niveau'),
            $request->query->get('categorie')
        );

        return $this->render('playlist/indexFront.html.twig', [
            'playlists' => $playlists,
        ]);
    }

    // ➕ AJOUTER UNE PLAYLIST
    #[Route('/new', name: 'playlist_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $playlist = new Playlist();
        $form     = $this->createForm(PlaylistType::class, $playlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $safeFilename = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
                $newFilename  = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('playlist_images_directory'), $newFilename);
                    $playlist->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            } else {
                $this->addFlash('error', 'L\'image est obligatoire');
                return $this->render('playlist/new.html.twig', ['form' => $form->createView()]);
            }

            $em->persist($playlist);
            $em->flush();

            $this->addFlash('success', 'Playlist créée avec succès !');
            return $this->redirectToRoute('playlist_index');
        }

        return $this->render('playlist/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // 👀 AFFICHER UNE PLAYLIST + VIDÉOS (admin)
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
            'videos'   => $videos,
        ]);
    }

    // 👀 AFFICHER UNE PLAYLIST + VIDÉOS (front)
    #[Route('/Front/{id}', name: 'playlistFront_show', requirements: ['id' => '\d+'])]
    public function showFront(
        Playlist $playlist,
        Request $request,
        CoachingVideoRepository $videoRepository
    ): Response {
        $videos = $videoRepository->searchInPlaylist(
            $playlist->getId(),
            $request->query->get('q'),
            $request->query->get('niveau')
        );

        return $this->render('playlist/showFront.html.twig', [
            'playlist' => $playlist,
            'videos'   => $videos,
        ]);
    }

    // ✏️ MODIFIER UNE PLAYLIST
    #[Route('/{id}/edit', name: 'playlist_edit', requirements: ['id' => '\d+'])]
    public function edit(
        Playlist $playlist,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(PlaylistType::class, $playlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $oldImage = $playlist->getImage();
                if ($oldImage) {
                    $oldPath = $this->getParameter('playlist_images_directory') . '/' . $oldImage;
                    if (file_exists($oldPath)) unlink($oldPath);
                }

                $safeFilename = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
                $newFilename  = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('playlist_images_directory'), $newFilename);
                    $playlist->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            $em->flush();
            $this->addFlash('success', 'Playlist modifiée avec succès !');
            return $this->redirectToRoute('playlist_index');
        }

        return $this->render('playlist/edit.html.twig', [
            'form'     => $form->createView(),
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
        if ($this->isCsrfTokenValid('delete' . $playlist->getId(), $request->request->get('_token'))) {
            $imagePath = $playlist->getImage();
            if ($imagePath) {
                $fullPath = $this->getParameter('playlist_images_directory') . '/' . $imagePath;
                if (file_exists($fullPath)) unlink($fullPath);
            }

            $em->remove($playlist);
            $em->flush();
            $this->addFlash('success', 'Playlist supprimée avec succès !');
        }

        return $this->redirectToRoute('playlist_index');
    }
}
