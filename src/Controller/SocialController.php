<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Commentaire;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Friend;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SocialController extends AbstractController
{
    #[Route('/social', name: 'social_index')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        PostRepository $postRepo
    ): Response {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $defaultUser = $this->getUser();

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                $post->setUser($defaultUser);

                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $fileName = uniqid() . '.' . $imageFile->guessExtension();
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $fileName
                    );
                    $post->setImage($fileName);
                }

                $em->persist($post);
                $em->flush();

                $this->addFlash('success', 'Publication ajoutée avec succès');
                return $this->redirectToRoute('social_index');

            } else {
                $this->addFlash('error', 'Veuillez corriger les erreurs du formulaire');
            }
        }

        $posts = $postRepo->findSmartFeed($defaultUser);

        $editPostForms = [];
        foreach ($posts as $p) {
            $editForm = $this->createForm(PostType::class, $p, [
                'action' => $this->generateUrl('post_edit', ['id' => $p->getId()]),
                'method' => 'POST',
            ]);
            $editPostForms[$p->getId()] = $editForm->createView();
        }

        $friendIds = array_map(
            fn($f) => $f->getFriend()->getId(),
            $em->getRepository(Friend::class)->findBy(['user' => $defaultUser])
        );

        $suggestedUsers = $em->getRepository(User::class)->createQueryBuilder('u')
            ->where('u != :currentUser')
            ->andWhere('u.id NOT IN (:friendIds)')
            ->setParameter('currentUser', $defaultUser)
            ->setParameter('friendIds', $friendIds ?: [0])
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();

        $notifications = $em->getRepository(Notification::class)
            ->findBy(['receiver' => $defaultUser], ['createdAt' => 'DESC']);

        return $this->render('social/index.html.twig', [
            'posts' => $posts,
            'form' => $form->createView(),
            'editPostForms' => $editPostForms,
            'defaultUser' => $defaultUser,
            'suggestedUsers' => $suggestedUsers,
            'notifications' => $notifications,
        ]);
    }

#[Route('/post/{id}/edit', name: 'post_edit', methods: ['POST'])]
public function editPost(
    Post $post,
    Request $request,
    EntityManagerInterface $em
): Response {

    $currentUser = $this->getUser();

    // 🔐 Sécurité : seul le propriétaire peut modifier
    if ($post->getUser() !== $currentUser) {
        throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce post.');
    }

    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'Publication modifiée avec succès !');
    } elseif ($form->isSubmitted()) {
        $this->addFlash('error', 'Erreur lors de la validation du formulaire');
    }

    return $this->redirectToRoute('social_index');
}#[Route('/post/delete/{id}', name: 'post_delete')]
public function deletePost(Post $post, EntityManagerInterface $em): Response
{
    $currentUser = $this->getUser();

    // 🔐 Sécurité : seul le propriétaire peut supprimer
    if ($post->getUser() !== $currentUser) {
        throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce post.');
    }

    $em->remove($post);
    $em->flush();

    $this->addFlash('success', 'Publication supprimée.');
    return $this->redirectToRoute('social_index');
}

    #[Route('/comment/add', name: 'comment_add', methods: ['POST'])]
    public function addComment(
        Request $request,
        EntityManagerInterface $em,
        PostRepository $postRepo,
        ValidatorInterface $validator
    ): Response {

        $defaultUser = $this->getUser();

        $text = trim($request->request->get('text'));
        $postId = $request->request->get('post_id');
        $post = $postRepo->find($postId);

        if (!$post) {
            return $this->redirectToRoute('social_index');
        }

        $comment = new Commentaire();
        $comment->setText($text);
        $comment->setPost($post);
        $comment->setUser($defaultUser);

        $errors = $validator->validate($comment);

        if (count($errors) > 0) {
            $this->addFlash('error', $errors[0]->getMessage());
            return $this->redirectToRoute('social_index');
        }

        $em->persist($comment);

        $notif = new Notification();
        $notif->setSender($defaultUser);
        $notif->setReceiver($post->getUser());
        $notif->setPost($post);
        $notif->setType('COMMENT');
        $notif->setIsRead(false);
        $notif->setCreatedAt(new \DateTimeImmutable());
        $em->persist($notif);

        $em->flush();

        $this->addFlash('success', 'Commentaire ajouté');
        return $this->redirectToRoute('social_index');
    }

    #[Route('/like/{id}', name: 'post_like')]
    public function like(Post $post, EntityManagerInterface $em): Response
    {
        $defaultUser = $this->getUser();

        if ($post->isLikedBy($defaultUser)) {
            $post->removeLikedBy($defaultUser);
        } else {
            $post->addLikedBy($defaultUser);

            $notif = new Notification();
            $notif->setSender($defaultUser);
            $notif->setReceiver($post->getUser());
            $notif->setPost($post);
            $notif->setType('LIKE');
            $notif->setIsRead(false);
            $notif->setCreatedAt(new \DateTimeImmutable());
            $em->persist($notif);
        }

        $em->flush();
        return $this->redirectToRoute('social_index');
    }

    #[Route('/social/search', name: 'social_search')]
    public function search(Request $request, UserRepository $userRepository): Response
    {
        $term = $request->query->get('q', '');
        $users = $term ? $userRepository->findByNameOrEmail($term) : [];

        return $this->render('social/search.html.twig', [
            'users' => $users,
            'term' => $term,
        ]);
    }

    #[Route('/social/friends', name: 'social_friends')]
    public function friends(EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        $friends = $em->getRepository(Friend::class)->findBy([
            'user' => $currentUser
        ]);

        return $this->render('social/friends.html.twig', [
            'friends' => $friends
        ]);
    }

    #[Route('/friend/add/{id}', name: 'friend_add')]
    public function addFriend(User $friendUser, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser || $currentUser === $friendUser) {
            return $this->redirectToRoute('social_index');
        }

        $existing = $em->getRepository(Friend::class)->findOneBy([
            'user' => $currentUser,
            'friend' => $friendUser
        ]);

        if (!$existing) {
            $friend = new Friend();
            $friend->setUser($currentUser);
            $friend->setFriend($friendUser);
            $friend->setCreatedAt(new \DateTimeImmutable());

            $em->persist($friend);
            $em->flush();
        }

        return $this->redirectToRoute('social_index');
    }

    #[Route('/notifications', name: 'social_notifications')]
    public function notifications(EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        $notifications = $em->getRepository(Notification::class)
            ->findBy(['receiver' => $currentUser], ['createdAt' => 'DESC']);

        return $this->render('social/notifications.html.twig', [
            'notifications' => $notifications
        ]);
    }

    #[Route('/notification/read/{id}', name: 'notification_read')]
    public function readNotification(Notification $notification, EntityManagerInterface $em): Response
    {
        $notification->setIsRead(true);
        $em->flush();

        return $this->redirectToRoute('social_notifications');
    }

    #[Route('/notification/delete/{id}', name: 'notification_delete', methods: ['POST'])]
    public function deleteNotification(Notification $notification, EntityManagerInterface $em): Response
    {
        $em->remove($notification);
        $em->flush();

        $this->addFlash('success', 'Notification supprimée avec succès !');
        return $this->redirectToRoute('social_notifications');
    }
    // Modifier un commentaire
#[Route('/comment/edit/{id}', name: 'comment_edit', methods: ['POST'])]
public function editComment(
    Commentaire $comment,
    Request $request,
    EntityManagerInterface $em,
    ValidatorInterface $validator
): Response {
    $currentUser = $this->getUser();

    // 🔐 Seul le propriétaire peut modifier
    if ($comment->getUser() !== $currentUser) {
        throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce commentaire.');
    }

    $text = trim($request->request->get('text'));
    $comment->setText($text);

    $errors = $validator->validate($comment);
    if (count($errors) > 0) {
        $this->addFlash('error', $errors[0]->getMessage());
    } else {
        $em->flush();
        $this->addFlash('success', 'Commentaire modifié avec succès !');
    }

    return $this->redirectToRoute('social_index');
}

// Supprimer un commentaire
#[Route('/comment/delete/{id}', name: 'comment_delete', methods: ['POST'])]
public function deleteComment(Commentaire $comment, EntityManagerInterface $em): Response {
    $currentUser = $this->getUser();

    if ($comment->getUser() !== $currentUser) {
        throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce commentaire.');
    }

    $em->remove($comment);
    $em->flush();

    $this->addFlash('success', 'Commentaire supprimé avec succès !');
    return $this->redirectToRoute('social_index');
}}