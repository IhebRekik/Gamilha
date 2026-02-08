<?php

namespace App\Controller\Front;

use App\Repository\EquipeRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Entity\Equipe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('', name: 'front_')]
class EquipeController extends AbstractController
{
    #[Route('/equipes', name: 'equipe_index', methods: ['GET'])]
    public function index(EquipeRepository $equipeRepository): Response
    {
        $equipes = $equipeRepository->findBy(
            [],
            ['nomEquipe' => 'ASC']
        );

        return $this->render('front/equipe/index.html.twig', [
            'equipes' => $equipes,
        ]);
    }

    #[Route('/equipes/{idEquipe}', name: 'equipe_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['idEquipe' => 'idEquipe'])] Equipe $equipe): Response
    {
        return $this->render('front/equipe/show.html.twig', [
            'equipe' => $equipe,
        ]);
    }
}
