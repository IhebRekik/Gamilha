<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\User1Type;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(User1Type::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('profileImage')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'. $imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('profiles_directory'), $newFilename);
                } catch (FileException $e) {
                    // ignore or log
                }
                $user->setProfileImage($newFilename);
            }
               $user->setPassword(
            $passwordHasher->hashPassword($user, $form->get('password')->getData())
        );
            if ($form->get('password')->getData()) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $form->get('password')->getData())
                );
            }
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(User1Type::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('profileImage')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'. $imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('profiles_directory'), $newFilename);
                } catch (FileException $e) {
                }
                $user->setProfileImage($newFilename);
            }
            if ($form->get('password')->getData()) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $form->get('password')->getData())
                );
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin/user/{id}/ban', name: 'admin_user_ban', methods: ['POST'])]
    public function ban(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('ban'.$user->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_user_index');
        }

        $user->setBanUntil(new \DateTimeImmutable('+2 days'));
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur banni pour 2 jours.');

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin/user/{id}/report', name: 'admin_user_report', methods: ['POST'])]
    public function report(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('report'.$user->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_user_index');
        }

        $user->setReports($user->getReports() + 1);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur signalé.');

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin/user/{id}/toggle-status', name: 'admin_user_toggle_status', methods: ['POST'])]
    public function toggleStatus(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('toggle_status'.$user->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_user_index');
        }

        $user->setIsActive(!$user->isActive());
        $entityManager->flush();

        $this->addFlash('success', 'Statut utilisateur mis à jour.');

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
