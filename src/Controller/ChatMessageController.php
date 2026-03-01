<?php

namespace App\Controller;

use App\Entity\ChatMessage;
use App\Entity\User;
use App\Form\ChatMessageType;
use App\Repository\ChatMessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/chat/message')]
 class ChatMessageController extends AbstractController
{
    #[Route(name: 'app_chat_message_index', methods: ['GET'])]
    public function index(ChatMessageRepository $chatMessageRepository): Response
    {
        return $this->render('chat_message/index.html.twig', [
            'chat_messages' => $chatMessageRepository->findAll(),
        ]);
    }

    #[Route('/new/{recipientId}', name: 'app_chat_message_new', methods: ['GET', 'POST'])]
    public function new(
        int $recipientId,
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $chatMessage = new ChatMessage();
        $form = $this->createForm(ChatMessageType::class, $chatMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userId = $request->cookies->get('user_email');

            if (!$userId) {
                throw new \Exception('user_email cookie not found');
            }

            $sender = $userRepository->findOneBy(['email' => $userId]);

            if (!$sender) {
                throw new \Exception('Sender not found');
            }

            $chatMessage->setSender($sender);
            $chatMessage->setRecipient($userRepository->find($recipientId));
            $chatMessage->setCreatedAt(new \DateTime());
            $entityManager->persist($chatMessage);
            $entityManager->flush();

            return $this->redirectToRoute('app_chat_message_index');
        }

        return $this->render('chat_message/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}', name: 'app_chat_message_show', methods: ['GET'])]
    public function show(ChatMessage $chatMessage): Response
    {
        return $this->render('chat_message/show.html.twig', [
            'chat_message' => $chatMessage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_chat_message_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ChatMessage $chatMessage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChatMessageType::class, $chatMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_chat_message_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('chat_message/edit.html.twig', [
            'chat_message' => $chatMessage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_chat_message_delete', methods: ['POST'])]
    public function delete(Request $request, ChatMessage $chatMessage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $chatMessage->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($chatMessage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_chat_message_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/conversation/{recipientId}', name: 'chat_conversation', methods: ['GET', 'POST'])]
    public function conversation(
        int $recipientId,
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        ChatMessageRepository $chatMessageRepository
    ): Response {

        $sender = $userRepository->findOneBy(['email' => $request->cookies->get('user_email')]);

       
        $recipient = $userRepository->find($recipientId);

        if (!$sender || !$recipient) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        // Nouveau message via formulaire
        $chatMessage = new ChatMessage();
        $form = $this->createForm(ChatMessageType::class, $chatMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chatMessage->setSender($sender);
            $chatMessage->setRecipient($recipient);
            $chatMessage->setCreatedAt(new \DateTime());

            $entityManager->persist($chatMessage);
            $entityManager->flush();

            // recharger la conversation
            return $this->redirectToRoute('chat_conversation', ['recipientId' => $recipientId]);
        }

        // Récupérer tous les messages entre sender et recipient
        $messages = $chatMessageRepository->createQueryBuilder('m')
            ->where('(m.sender = :sender AND m.recipient = :recipient)')
            ->orWhere('(m.sender = :recipient AND m.recipient = :sender)')
            ->setParameter('sender', $sender)
            ->setParameter('recipient', $recipient)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('chat_message/conversation.html.twig', [
            'messages' => $messages,
            'form' => $form->createView(),
            'recipient' => $recipient,
        ]);
    }
    #[Route('/conversation/edit/{id}', name: 'chat_conversation_edit', methods: ['POST'])]
    public function editAjax(
        ChatMessage $message,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $newContent = $data['content'] ?? '';

        $message->setContent($newContent);
        $em->flush();

        return new JsonResponse(['status' => 'success']);
    }
    #[Route('/conversation/delete/{id}', name: 'chat_conversation_delete', methods: ['POST'])]
    public function deleteConversation(
        ChatMessage $message,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        if (!$this->isCsrfTokenValid('delete' . $message->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $currentUser = $this->getUser();
        /** @var User|null $currentUser */



        $recipientId = $message->getRecipient()->getId();

        $entityManager->remove($message);
        $entityManager->flush();

        return $this->redirectToRoute('chat_conversation', [
            'recipientId' => $recipientId
        ]);
    }
}
