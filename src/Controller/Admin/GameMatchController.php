<?php

namespace App\Controller\Admin;

use App\Entity\GameMatch;
use App\Form\GameMatchType;
use App\Repository\GameMatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/match')]
final class GameMatchController extends AbstractController
{
    #[Route(name: 'admin_match_index', methods: ['GET'])]
    public function index(GameMatchRepository $gameMatchRepository): Response
    {
        return $this->render('admin/match/index.html.twig', [
            'matchs' => $gameMatchRepository->findBy([], ['idMatch' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_match_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $gameMatch = new GameMatch();
        $form = $this->createForm(GameMatchType::class, $gameMatch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($gameMatch);
            $entityManager->flush();

            return $this->redirectToRoute('admin_match_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/match/new.html.twig', [
            'match' => $gameMatch,
            'form' => $form,
            'edit' => false,
        ]);
    }

    #[Route('/{idMatch}', name: 'admin_match_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['idMatch' => 'idMatch'])] GameMatch $gameMatch): Response
    {
        return $this->render('admin/match/show.html.twig', [
            'match' => $gameMatch,
        ]);
    }

    #[Route('/{idMatch}/edit', name: 'admin_match_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[MapEntity(mapping: ['idMatch' => 'idMatch'])] GameMatch $gameMatch, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GameMatchType::class, $gameMatch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_match_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/match/edit.html.twig', [
            'match' => $gameMatch,
            'form' => $form,
            'edit' => true,
        ]);
    }

    #[Route('/{idMatch}', name: 'admin_match_delete', methods: ['POST'])]
    public function delete(Request $request, #[MapEntity(mapping: ['idMatch' => 'idMatch'])] GameMatch $gameMatch, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->getString('_token');
        if ($token && $this->isCsrfTokenValid('delete'.$gameMatch->getIdMatch(), $token)) {
            $entityManager->remove($gameMatch);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_match_index', [], Response::HTTP_SEE_OTHER);
    }
}
