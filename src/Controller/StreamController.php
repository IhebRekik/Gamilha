<?php

namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Stream;
use App\Form\StreamType;
use App\Repository\StreamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class StreamController extends AbstractController
{
#[Route('/stream', name: 'stream_index')]
public function index(Request $request, EntityManagerInterface $em, StreamRepository $repo): Response
{
    // Formulaire
    $stream = new Stream();
    $form = $this->createForm(StreamType::class, $stream);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $stream->setStatus('live');
        $stream->setViewers(0);

        $em->persist($stream);
        $em->flush();

        return $this->redirectToRoute('stream_index');
    }

    return $this->render('stream/index.html.twig', [
        'streams' => $repo->findAll(),
        'form'    => $form->createView(),

        // ✅ IMPORTANT pour éviter les erreurs Twig
        'query' => '',
        'game'  => '',
        'sort'  => '',
    ]);
}#[Route('/stream', name: 'stream_index')]
public function index(Request $request, EntityManagerInterface $em, StreamRepository $repo): Response
{
    // Création du formulaire
    $stream = new Stream();
    $form = $this->createForm(StreamType::class, $stream);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $stream->setStatus('live');
        $stream->setViewers(0);

        $em->persist($stream);
        $em->flush();

        return $this->redirectToRoute('stream_index');
    }

    // Récupération des streams
    $streams = $repo->findAll();

    // Récupération des paramètres de recherche optionnels
    $query = $request->query->get('q', '');
    $game  = $request->query->get('game', '');
    $sort  = $request->query->get('sort', '');

    return $this->render('stream/index.html.twig', [
        'streams' => $streams,
        'form' => $form->createView(),
        'query' => $query,
        'game' => $game,
        'sort' => $sort,
    ]);
}

#[Route('/stream/discover', name: 'stream_discover')]
public function discover(Request $request, StreamRepository $repo): Response
{
    $query = $request->query->get('q', '');
    $game  = $request->query->get('game', '');
    $sort  = $request->query->get('sort', '');

    $streams = $repo->searchLiveStreams($query, $game, $sort);

    return $this->render('stream/index.html.twig', [
        'streams' => $streams,
        'query'   => $query,
        'game'    => $game,
        'sort'    => $sort,
        'form'    => null, // pas de formulaire ici
    ]);
}

}