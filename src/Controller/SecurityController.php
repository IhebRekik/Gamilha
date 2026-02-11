<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use function PHPUnit\Framework\containsEqual;

class SecurityController extends AbstractController
{
    #[Route('/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        if ($user instanceof \App\Entity\User) {
            // Redirection selon le rôle
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $response = $this->redirectToRoute('admin_dashboard');
            } else {
                $response = $this->redirectToRoute('app_homepage');
            }
            $emailFromRequest = $user->getEmail(); // ex: "user2%40gmail.com"
            $email = urldecode($emailFromRequest);
            // Créer le cookie avec l'email
            $cookie = Cookie::create('user_email')
                ->withValue($email)
                ->withExpires(strtotime('+1 day'))
                ->withSecure(false)   // mettre true si HTTPS
                ->withHttpOnly(true);

            // Ajouter le cookie à la réponse
            $response->headers->setCookie($cookie);

            return $response;
        }

        // Formulaire de login si pas encore connecté
        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }


    #[Route('/logout', name: 'app_logout')]
    public function logout(): Response
    {
        $response =  $this->redirectToRoute('app_login');

    // Supprimer le cookie "user_email"
    $cookie = Cookie::create('user_email')
        ->withValue('')                // vide la valeur
        ->withExpires(strtotime('-1 day')) // date passée pour supprimer
        ->withHttpOnly(true)
        ->withSecure(false);           // true si HTTPS

    $response->headers->setCookie($cookie);
        return $response;
    }

    private function redirectAfterLogin(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->redirectToRoute('app_login');
    }
}
