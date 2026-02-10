<?php

namespace App\Controller\Front;

use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Entity\Evenement;

#[Route('', name: 'front_')]
class EvenementController extends AbstractController
{
    #[Route('/evenements', name: 'evenement_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository): Response
    {
        $evenements = $evenementRepository->findBy(
            [],
            ['dateDebut' => 'DESC']
        );

        return $this->render('front/evenement/index.html.twig', [
            'evenements' => $evenements,
        ]);
    }

    #[Route('/evenements/{idEvenement}', name: 'evenement_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['idEvenement' => 'idEvenement'])] Evenement $evenement): Response
    {
        return $this->render('front/evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }
}
