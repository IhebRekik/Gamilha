<?php

namespace App\Controller\Admin;

use App\Entity\Bracket;
use App\Form\BracketType;
use App\Repository\BracketRepository;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/bracket')]
final class BracketController extends AbstractController
{
    #[Route(name: 'admin_bracket_index', methods: ['GET'])]
    public function index(BracketRepository $bracketRepository): Response
    {
        return $this->render('admin/bracket/index.html.twig', [
            'brackets' => $bracketRepository->findBy([], ['idBracket' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_bracket_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EvenementRepository $evenementRepository): Response
    {
        $bracket = new Bracket();
        
        // Si un idEvenement est fourni dans la query string, pré-remplir l'événement
        $idEvenement = $request->query->getInt('idEvenement', 0);
        if ($idEvenement > 0) {
            $evenement = $evenementRepository->find($idEvenement);
            if ($evenement) {
                $bracket->setEvenement($evenement);
            }
        }
        
        $form = $this->createForm(BracketType::class, $bracket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($bracket);
            $entityManager->flush();

            return $this->redirectToRoute('admin_bracket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/bracket/new.html.twig', [
            'bracket' => $bracket,
            'form' => $form,
            'edit' => false,
        ]);
    }

    #[Route('/{idBracket}', name: 'admin_bracket_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['idBracket' => 'idBracket'])] Bracket $bracket): Response
    {
        $matchsByTour = [];
        foreach ($bracket->getMatchs() as $match) {
            $tour = $match->getTour();
            if (!isset($matchsByTour[$tour])) {
                $matchsByTour[$tour] = [];
            }
            $matchsByTour[$tour][] = $match;
        }
        ksort($matchsByTour);
        foreach ($matchsByTour as $tour => $matchs) {
            usort($matchsByTour[$tour], fn ($a, $b) => $a->getIdMatch() <=> $b->getIdMatch());
        }

        return $this->render('admin/bracket/show.html.twig', [
            'bracket' => $bracket,
            'matchsByTour' => $matchsByTour,
            'rounds' => array_keys($matchsByTour),
        ]);
    }

    #[Route('/{idBracket}/edit', name: 'admin_bracket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[MapEntity(mapping: ['idBracket' => 'idBracket'])] Bracket $bracket, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BracketType::class, $bracket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_bracket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/bracket/edit.html.twig', [
            'bracket' => $bracket,
            'form' => $form,
            'edit' => true,
        ]);
    }

    #[Route('/{idBracket}', name: 'admin_bracket_delete', methods: ['POST'])]
    public function delete(Request $request, #[MapEntity(mapping: ['idBracket' => 'idBracket'])] Bracket $bracket, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->getString('_token');
        if ($token && $this->isCsrfTokenValid('delete'.$bracket->getIdBracket(), $token)) {
            $entityManager->remove($bracket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_bracket_index', [], Response::HTTP_SEE_OTHER);
    }
}
