<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Commentaire;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Friend;
use App\Repository\FriendRepository;

class SocialController extends AbstractController
{
    // =========================
    // FEED + AJOUT POST
    // =========================
    #[Route('/social', name: 'social_index')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        PostRepository $postRepo

    ): Response {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

      if ($form->isSubmitted()) {

    if ($form->isValid()) {

        $user = $em->getRepository(User::class)->find(1);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur par défaut introuvable');
        }
        $post->setUser($user);

        // Image
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
        // 👇 PAS DE PAGE ROUGE
        $this->addFlash('error', 'Veuillez corriger les erreurs du formulaire');
    }
}

      // === NOUVEAU : formulaires d'édition pour chaque post (pour les modals) ===
    $posts = $postRepo->findBy([], ['createdAt' => 'DESC']);
    $editPostForms = [];
    foreach ($posts as $p) {
        $editForm = $this->createForm(PostType::class, $p, [
            'action' => $this->generateUrl('post_edit', ['id' => $p->getId()]),
            'method' => 'POST',
        ]);
        $editPostForms[$p->getId()] = $editForm->createView();
    }
// Récupère l'utilisateur par défaut (id = 1)
$defaultUser = $em->getRepository(User::class)->find(1);
$friendIds = array_map(fn($f) => $f->getFriend()->getId(), $em->getRepository(Friend::class)->findBy(['user' => $defaultUser]));

$suggestedUsers = $em->getRepository(User::class)->createQueryBuilder('u')
    ->where('u != :currentUser')
    ->andWhere('u.id NOT IN (:friendIds)')
    ->setParameter('currentUser', $defaultUser)
    ->setParameter('friendIds', $friendIds ?: [0])
    ->setMaxResults(4)
    ->getQuery()
    ->getResult();

    return $this->render('social/index.html.twig', [
        'posts' => $posts,
        'form' => $form->createView(),
        'editPostForms' => $editPostForms,   // ← passé au Twig
        'defaultUser' => $defaultUser,   // ← AJOUTE CETTE LIGNE
            'suggestedUsers' => $suggestedUsers,  // ← ici

    ]);}
    // =========================
    // EDIT POST
    // =========================
   #[Route('/post/{id}/edit', name: 'post_edit', methods: ['POST'])]
public function editPost(
    Post $post,
    Request $request,
    EntityManagerInterface $em
): Response {
    // Utilisateur par défaut si jamais
    $defaultUser = $em->getRepository(User::class)->find(1);
    if (!$post->getUser()) {
        $post->setUser($defaultUser);
    }

    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'Publication modifiée avec succès !');
    } else if ($form->isSubmitted()) {
        $this->addFlash('error', 'Erreur lors de la validation du formulaire');
    }

    return $this->redirectToRoute('social_index');
}
    // =========================
    // DELETE POST
    // =========================
    #[Route('/post/delete/{id}', name: 'post_delete')]
    public function deletePost(Post $post, EntityManagerInterface $em): Response
    {
        if (!$post->getUser()) {
            $post->setUser($em->getRepository(User::class)->find(1));
        }

        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('social_index');
    }

    // =========================
    // AJOUT COMMENTAIRE
    // =========================
    #[Route('/comment/add', name: 'comment_add', methods: ['POST'])]
    public function addComment(
        Request $request,
        EntityManagerInterface $em,
        PostRepository $postRepo
    ): Response {
        $text = trim($request->request->get('text'));
        $postId = $request->request->get('post_id');

        if (!$text || !$postId) {
            return $this->redirectToRoute('social_index');
        }

        $post = $postRepo->find($postId);
        if (!$post) {
            return $this->redirectToRoute('social_index');
        }

        $user = $em->getRepository(User::class)->find(1);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur par défaut introuvable');
        }

        $comment = new Commentaire();
        $comment->setText($text);
        $comment->setPost($post);
        $comment->setUser($user);

        $em->persist($comment);
        $em->flush();

        return $this->redirectToRoute('social_index');
    }

    // =========================
    // EDIT COMMENTAIRE
    // =========================
    #[Route('/comment/{id}/edit', name: 'comment_edit')]
    public function editComment(
        Commentaire $comment,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!$comment->getUser()) {
            $comment->setUser($em->getRepository(User::class)->find(1));
        }

        if ($request->isMethod('POST')) {
            $text = trim($request->request->get('text'));
            if ($text) {
                $comment->setText($text);
                $em->flush();
            }
            return $this->redirectToRoute('social_index');
        }

        return $this->redirectToRoute('social_index');}

    // =========================
    // DELETE COMMENTAIRE
    // =========================
    #[Route('/comment/delete/{id}', name: 'comment_delete')]
    public function deleteComment(
        Commentaire $comment,
        EntityManagerInterface $em
    ): Response {
        if (!$comment->getUser()) {
            $comment->setUser($em->getRepository(User::class)->find(1));
        }

        $em->remove($comment);
        $em->flush();

        return $this->redirectToRoute('social_index');
    }

    // =========================
    // LIKE POST
    // =========================
  #[Route('/like/{id}', name: 'post_like')]
public function like(
    Post $post,
    EntityManagerInterface $em
): Response {
    $user = $em->getRepository(User::class)->find(1);
    if (!$user) {
        throw $this->createNotFoundException('Utilisateur par défaut introuvable');
    }

    if ($post->isLikedBy($user)) {
        $post->removeLikedBy($user);
    } else {
        $post->addLikedBy($user);
    }

    $em->flush();
    
    return $this->redirectToRoute('social_index');
}
    // =========================
    // SEARCH USERS
    // =========================
    #[Route('/social/search', name: 'social_search')]
    public function search(
        Request $request,
        UserRepository $userRepository
    ): Response {
        $term = $request->query->get('q', '');
        $users = $term ? $userRepository->findByNameOrEmail($term) : [];

        return $this->render('social/search.html.twig', [
            'users' => $users,
            'term' => $term,
        ]);
    }
   #[Route('/social/friends', name: 'social_friends')]
public function friends(
    EntityManagerInterface $em
): Response {
    $currentUser = $em->getRepository(User::class)->find(1);

    $friends = $em->getRepository(Friend::class)->findBy([
        'user' => $currentUser
    ]);

    return $this->render('social/friends.html.twig', [
        'friends' => $friends
    ]);
}

#[Route('/friend/add/{id}', name: 'friend_add')]
public function addFriend(
    User $friendUser,
    EntityManagerInterface $em
): Response {
    // utilisateur par défaut (temporaire)
    $currentUser = $em->getRepository(User::class)->find(1);

    if (!$currentUser || $currentUser === $friendUser) {
        return $this->redirectToRoute('social_index');
    }

    // éviter doublon
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

}
