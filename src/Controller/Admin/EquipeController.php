<?php

namespace App\Controller\Admin;

use App\Entity\Equipe;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/equipe')]
final class EquipeController extends AbstractController
{
    #[Route(name: 'admin_equipe_index', methods: ['GET'])]
    public function index(EquipeRepository $equipeRepository): Response
    {
        return $this->render('admin/equipe/index.html.twig', [
            'equipes' => $equipeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_equipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipe);
            $entityManager->flush();

            return $this->redirectToRoute('admin_equipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/equipe/new.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
            'edit' => false,
        ]);
    }

    #[Route('/{idEquipe}', name: 'admin_equipe_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['idEquipe' => 'idEquipe'])] Equipe $equipe): Response
    {
        return $this->render('admin/equipe/show.html.twig', [
            'equipe' => $equipe,
        ]);
    }

    #[Route('/{idEquipe}/edit', name: 'admin_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[MapEntity(mapping: ['idEquipe' => 'idEquipe'])] Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_equipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
            'edit' => true,
        ]);
    }

    #[Route('/{idEquipe}', name: 'admin_equipe_delete', methods: ['POST'])]
    public function delete(Request $request, #[MapEntity(mapping: ['idEquipe' => 'idEquipe'])] Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->getString('_token');
        if ($token && $this->isCsrfTokenValid('delete'.$equipe->getIdEquipe(), $token)) {
            $entityManager->remove($equipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_equipe_index', [], Response::HTTP_SEE_OTHER);
    }
}
