<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListUtilisateurController extends AbstractController
{

#[Route('/chat/users', name: 'chat_user_list')]
public function listUsers(
    Request $request,
    UserRepository $userRepository
): Response {
    $cookieUserId = $request->cookies->get('user_id');

    $users = $userRepository->createQueryBuilder('u')
        ->andWhere('u.id != :cookieId')
        ->setParameter('cookieId', $cookieUserId)
        ->getQuery()
        ->getResult();

    return $this->render('chat_message/user_list.html.twig', [
        'users' => $users,
    ]);
}

}
