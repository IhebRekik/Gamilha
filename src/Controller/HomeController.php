<?php
// 📁 src/Controller/HomeController.php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_homepage')]
    public function index(
        UserRepository $userRepository,
        EvenementRepository $evenementRepository,
        Request $request
    ): Response {
 $evenements = $evenementRepository->findBy([], ['dateDebut' => 'DESC'], 6); 
                    return $this->render('home/index.html.twig', [
                        'evenements' => $evenements,
                    ]);}
}
