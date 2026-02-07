<?php

namespace App\Controller;

use App\Entity\Stream;
use App\Form\Stream1Type;
use App\Repository\StreamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stream/crud')]
final class StreamCrudController extends AbstractController
{
    #[Route(name: 'app_stream_crud_index', methods: ['GET'])]
    public function index(StreamRepository $streamRepository): Response
    {
        return $this->render('stream_crud/index.html.twig', [
            'streams' => $streamRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_stream_crud_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $stream = new Stream();
        $form = $this->createForm(Stream1Type::class, $stream);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($stream);
            $entityManager->flush();

            return $this->redirectToRoute('app_stream_crud_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stream_crud/new.html.twig', [
            'stream' => $stream,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stream_crud_show', methods: ['GET'])]
    public function show(Stream $stream): Response
    {
        return $this->render('stream_crud/show.html.twig', [
            'stream' => $stream,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stream_crud_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stream $stream, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Stream1Type::class, $stream);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_stream_crud_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stream_crud/edit.html.twig', [
            'stream' => $stream,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stream_crud_delete', methods: ['POST'])]
    public function delete(Request $request, Stream $stream, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stream->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($stream);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_stream_crud_index', [], Response::HTTP_SEE_OTHER);
    }
}
