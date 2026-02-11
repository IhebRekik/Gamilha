<?php

namespace App\Controller;

use App\Entity\UserAbonnement;
use App\Form\UserAbonnementType;
use App\Repository\UserAbonnementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user/abonnement')]
final class UserAbonnementController extends AbstractController
{
    #[Route(name: 'app_user_abonnement_index', methods: ['GET'])]
    public function index(UserAbonnementRepository $userAbonnementRepository): Response
    {
        return $this->render('admin/user_abonnement/index.html.twig', [
            'user_abonnements' => $userAbonnementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_abonnement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userAbonnement = new UserAbonnement();
        $form = $this->createForm(UserAbonnementType::class, $userAbonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($userAbonnement);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_abonnement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user_abonnement/new.html.twig', [
            'user_abonnement' => $userAbonnement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_abonnement_show', methods: ['GET'])]
    public function show(UserAbonnement $userAbonnement): Response
    {
        return $this->render('admin/user_abonnement/show.html.twig', [
            'user_abonnement' => $userAbonnement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_abonnement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserAbonnement $userAbonnement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserAbonnementType::class, $userAbonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_abonnement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user_abonnement/edit.html.twig', [
            'user_abonnement' => $userAbonnement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_abonnement_delete', methods: ['POST'])]
    public function delete(Request $request, UserAbonnement $userAbonnement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userAbonnement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($userAbonnement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_abonnement_index', [], Response::HTTP_SEE_OTHER);
    }
    
}
