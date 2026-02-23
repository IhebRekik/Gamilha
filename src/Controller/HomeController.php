<?php
// 📁 src/Controller/HomeController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/front', name: 'app_homepagefront')]
    public function indexfront(): Response
    {
        return $this->render('base_front.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
