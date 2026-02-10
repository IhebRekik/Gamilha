<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(
        UserRepository $userRepository,
        EvenementRepository $evenementRepository,
        Request $request
    ): Response {
        // 1️⃣ Vérifier si le cookie 'user_email' existe
        $userEmail = $request->cookies->get('user_email');

        if ($userEmail) {
            // Récupérer l'utilisateur par email
            $user = $userRepository->createQueryBuilder('u')
                ->where('u.email = :email')
                ->setParameter('email', $userEmail)
                ->getQuery()
                ->getOneOrNullResult();

            if ($user) {
                // Vérifier le rôle admin
                if (in_array('ROLE_ADMIN', $user->getRoles())) {
                    print_r($user->getRoles());
                    return $this->redirectToRoute('admin_dashboard');
                } else if(in_array('ROLE_USER', $user->getRoles())) {
                    $evenements = #$evenementRepository->findBy([], ['dateDebut' => 'DESC'], 6); 
                        [];

                    return $this->render('home/index.html.twig', [
                        'evenements' => $evenements,
                    ]);
                    }else {
                        // Rôle inconnu, rediriger vers login
                        return $this->redirectToRoute('app_login');
                    }
                }
        }
        return $this->redirectToRoute('app_login');

        // 2️⃣ Récupérer les derniers événements

    }
}
