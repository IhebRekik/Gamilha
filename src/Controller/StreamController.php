<?php

namespace App\Controller;

use App\Entity\Stream;
use App\Form\StreamType;
use App\Repository\StreamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;


final class StreamController extends AbstractController
{

    #[Route('/stream', name: 'stream_index')]
    public function index(Request $request, EntityManagerInterface $em, StreamRepository $repo, UserRepository $userRepository): Response
    {
        // --- Création du formulaire pour lancer un stream ---
        $stream = new Stream();
        $form = $this->createForm(StreamType::class, $stream);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cookieUserId = $request->cookies->get('user_id');

            // Récupérer tous les utilisateurs sauf celui dans le cookie
        
            $user = $userRepository->find(1);
            $stream->setUser($user);
            $stream->setStatus('live');
            $stream->setViewers(0);

            $em->persist($stream);
            $em->flush();

            return $this->redirectToRoute('stream_index');
        }

        // --- Récupération des filtres GET ---
        $query = $request->query->get('q', '');
        $game = $request->query->get('game', '');
        $sort = $request->query->get('sort', '');

        // --- Construction de la requête filtrée ---
        $qb = $repo->createQueryBuilder('s');

        if ($query) {
            $qb->andWhere('s.title LIKE :query OR s.description LIKE :query')
                ->setParameter('query', "%$query%");
        }

        if ($game) {
            $qb->andWhere('s.game = :game')
                ->setParameter('game', $game);
        }

        if ($sort === 'viewers') {
            $qb->orderBy('s.viewers', 'DESC');
        } else {
            $qb->orderBy('s.createdAt', 'DESC'); // par défaut plus récents
        }

        $streams = $qb->getQuery()->getResult();

        // --- Rendu du template ---
        return $this->render('stream/index.html.twig', [
            'streams' => $streams,
            'form' => $form->createView(),
            'query' => $query,
            'game' => $game,
            'sort' => $sort,
        ]);
    }

    #[Route('/stream/{id}', name: 'stream_show', methods: ['GET'])]
    public function show(Stream $stream): Response
    {
        return $this->render('stream/show.html.twig', [
            'stream' => $stream,
        ]);
    }

}
