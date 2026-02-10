<?php
namespace App\Controller;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListUtilisateursController extends AbstractController
{
       private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }
#[Route('/chat/users', name: 'chat_user_list')]
public function listUsers(
    Request $request,
    UserRepository $userRepository
): Response {
    $cookieUserId = $request->cookies->get('user_email');

    // Récupérer tous les utilisateurs sauf celui dans le cookie
    $users = $userRepository->createQueryBuilder('u')
        ->andWhere('u.email != :cookieId')
        ->setParameter('cookieId', $cookieUserId)
        ->getQuery()
        ->getResult();

    return $this->render('chat_message/user_list.html.twig', [
        'users' => $users,
    ]);
}
#[Route('/chat/create/{recipientId}', name: 'chat_create_message')]
public function createMessage(
    int $recipientId,
    Request $request,
    UserRepository $userRepository,
    EntityManagerInterface $entityManager
): Response {
    $cookieUserId = $request->cookies->get('user_id');

    $sender = $userRepository->find($cookieUserId);
    $recipient = $userRepository->find($recipientId);

    if (!$sender || !$recipient) {
        throw $this->createNotFoundException('Utilisateur introuvable.');
    }

    $chatMessage = new \App\Entity\ChatMessage();
    $chatMessage->setSender($sender);
    $chatMessage->setRecipient($recipient);
    $chatMessage->setContent(''); // contenu vide, tu peux rediriger vers un form si besoin
    $chatMessage->setCreatedAt(new \DateTime());

    $entityManager->persist($chatMessage);
    $entityManager->flush();

    // redirection vers la liste de chat/messages ou formulaire
    return $this->redirectToRoute('app_chat_message_index');
}

}