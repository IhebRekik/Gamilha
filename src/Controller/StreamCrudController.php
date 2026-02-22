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
use App\Repository\UserRepository;
use App\Entity\User;
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
    public function new(Request $request, EntityManagerInterface $entityManager,UserRepository $userRepository): Response
    {
        $stream = new Stream();
        $form = $this->createForm(Stream1Type::class, $stream);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Assign user admin fixe
$email = $request->cookies->get('user_email');

$user = $userRepository->findOneBy([
    'email' => $email
]);
            if (!$user) {
                throw $this->createAccessDeniedException();
            }
            $stream->setUser($user);

            $entityManager->persist($stream);
            $entityManager->flush();

            $this->addFlash('success', 'Stream créé !');

            return $this->redirectToRoute('app_stream_crud_index');
        }

        return $this->render('stream_crud/new.html.twig', [
            'stream' => $stream,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stream_crud_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stream $stream, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Stream1Type::class, $stream);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ne jamais réassigner l'utilisateur
            $entityManager->flush();
            $this->addFlash('success', 'Stream mis à jour !');

            return $this->redirectToRoute('app_stream_crud_index');
        }

        return $this->render('stream_crud/edit.html.twig', [
            'stream' => $stream,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_stream_crud_delete', methods: ['POST'])]
    public function delete(Request $request, Stream $stream, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stream->getId(), $request->request->get('_token'))) {
            $entityManager->remove($stream);
            $entityManager->flush();
            $this->addFlash('success', 'Stream supprimé !');
        }

        return $this->redirectToRoute('app_stream_crud_index');
    }
}
   


