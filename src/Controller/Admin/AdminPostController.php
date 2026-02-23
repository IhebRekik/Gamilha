<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\Commentaire;

use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;



#[Route('/admin/post')]
final class AdminPostController extends AbstractController
{
    #[Route('/', name: 'admin_post_index', methods: ['GET'])]
public function index(Request $request, PostRepository $postRepository, PaginatorInterface $paginator
): Response
{
    $search = $request->query->get('q');
    $userSearch = $request->query->get('user');

    $query = $postRepository->createQueryBuilder('p')
        ->orderBy('p.id', 'DESC')
        ->getQuery();

    if($search) {
        $posts = $postRepository->findByContent($search);
    }
    if($userSearch) {
        $posts = $postRepository->findByUserEmail($userSearch);
    }
    $pagination = $paginator->paginate(
        $query, // query Doctrine
        $request->query->getInt('page', 1), // page actuelle
        4 // nombre de posts par page
    );


    return $this->render('admin/post/index.html.twig', [
        'search' => $search,
        'userSearch' => $userSearch,
        'pagination' => $pagination, // pour les liens de page

    ]);
}


    #[Route('/new', name: 'admin_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($this->getUser() ?? $em->getRepository('App\Entity\User')->find(1));
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('admin_post_index');
        }

        return $this->render('admin/post/new.html.twig', [
            'form' => $form,
        ]);
    }

#[Route('/{id}', name: 'admin_post_show', methods: ['GET'])]
public function show(Post $post): Response
{
    return $this->render('admin/post/show.html.twig', [
        'post' => $post,
    ]);
}    #[Route('/{id}/edit', name: 'admin_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_post_index');
        }

        return $this->render('admin/post/edit.html.twig', [
            'form' => $form,
            'post' => $post,
        ]);
    }

    #[Route('/{id}', name: 'admin_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('admin_post_index');
    }
  #[Route('/comment/{id}/delete', name: 'admin_comment_delete', methods: ['POST'])]
public function deleteComment(Commentaire $comment, EntityManagerInterface $em): Response
{
    $postId = $comment->getPost()->getId();
    $em->remove($comment);
    $em->flush();

    return $this->redirectToRoute('admin_post_show', ['id' => $postId]);
}



}
