<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $queryBuilder = $userRepository->createQueryBuilder('u');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            limit: 5  // items per page
        );

        return $this->render('admin/user/index.html.twig', [
            'pagination' => $pagination,   // ← change 'users' → 'pagination'
        ]);
    }

    #[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image de profil
            $imageFile = $form->get('profileImage')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'. $imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('profiles_directory'), $newFilename);
                    $user->setProfileImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            // Gestion du mot de passe (requis pour la création)
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès !');
            return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/new.html.twig', [
            'form'         => $form->createView(),
            'edit'         => false,
            'button_label' => 'Créer',
            'user'         => $user,
        ]);
    }
    

    #[Route('/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image de profil
            $imageFile = $form->get('profileImage')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'. $imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('profiles_directory'), $newFilename);
                    $user->setProfileImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            // Gestion du mot de passe (optionnel pour la mise à jour)
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur mis à jour avec succès !');
            return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/new.html.twig', [
            'form'         => $form->createView(),
            'edit'         => true,
            'button_label' => 'Mettre à jour',
            'user'         => $user,
        ]);
    }

    #[Route('/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/ban', name: 'admin_user_ban', methods: ['POST'])]
    public function ban(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('ban' . $user->getId(), $request->getPayload()->getString('_token'))) {
            // Récupérer la durée ou la date personnalisée
            $banDuration = $request->request->get('ban_duration');
            $customDate = $request->request->get('custom_ban_date');
            
           $banUntil = null;

                if ($customDate) {
                    try {
                        $banUntil = new \DateTimeImmutable($customDate);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Date invalide. Veuillez réessayer.');
                        return $this->redirectToRoute('admin_user_index');
                    }
                } elseif ($banDuration === 'permanent') {
                    $banUntil = new \DateTimeImmutable('+100 years');
                } elseif ($banDuration) {
                    $days = (int) $banDuration;
                    $banUntil = new \DateTimeImmutable("+{$days} days");
                } else {
                    $banUntil = new \DateTimeImmutable('+30 days');
                }

        $user->setBanUntil($banUntil);
        $entityManager->flush();
                    
            $banMessage = $banDuration === 'permanent' 
                ? sprintf('Utilisateur %s banni définitivement', $user->getName())
                : sprintf('Utilisateur %s banni jusqu\'au %s', $user->getName(), $banUntil->format('d/m/Y à H:i'));
            
            $this->addFlash('warning', $banMessage);
        }

        return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/unban', name: 'admin_user_unban', methods: ['POST'])]
    public function unban(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('unban' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $user->setBanUntil(null);
            $entityManager->flush();
            
            $this->addFlash('success', sprintf('Utilisateur %s débanni avec succès !', $user->getName()));
        }

        return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/toggle-active', name: 'admin_user_toggle_active', methods: ['POST'])]
    public function toggleActive(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle-active' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $user->setIsActive(!$user->isActive());
            $entityManager->flush();
            
            $status = $user->isActive() ? 'activé' : 'désactivé';
            $this->addFlash('info', sprintf('Compte de %s %s !', $user->getName(), $status));
        }

        return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
