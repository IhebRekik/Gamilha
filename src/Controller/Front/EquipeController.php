<?php

namespace App\Controller\Front;

use App\Entity\Equipe;
use App\Entity\User;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;
use App\Repository\EvenementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('', name: 'front_')]
class EquipeController extends AbstractController
{
    #[Route('/equipes', name: 'equipe_index', methods: ['GET'])]
    public function index(EquipeRepository $equipeRepository): Response
    {
        $equipes = $equipeRepository->findBy(
            [],
            ['nomEquipe' => 'ASC']
        );

        return $this->render('front/equipe/index.html.twig', [
            'equipes' => $equipes,
        ]);
    }

    #[Route('/mes-participations', name: 'equipe_my_participations', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myParticipations(EvenementRepository $evenementRepository): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }
        $evenements = $evenementRepository->findByUserParticipations($user);
        $eventsForCalendar = [];
        $participations = [];

        foreach ($evenements as $evt) {
            $equipesUser = [];
            foreach ($evt->getEquipesParticipantes() as $eq) {
                if ($eq->getMembers()->contains($user)) {
                    $equipesUser[] = $eq->getNomEquipe();
                }
            }
            $participations[] = ['evenement' => $evt, 'equipesUser' => $equipesUser];

            $dateFin = $evt->getDateFin();

            if ($dateFin instanceof \DateTime) {
                $endDate = (clone $dateFin)->modify('+1 day')->format('Y-m-d');
            } else {
                $endDate = null;
            }
            $eventsForCalendar[] = [
                'id' => $evt->getIdEvenement(),
                'title' => $evt->getNom() . ' (' . implode(', ', $equipesUser) . ')',
                'start' => $evt->getDateDebut()?->format('Y-m-d'),
                'end' => $endDate,
                'url' => $this->generateUrl('front_evenement_show', ['idEvenement' => $evt->getIdEvenement()]),
                'jeu' => $evt->getJeu(),
                'statut' => $evt->getStatut(),
                'equipes' => $equipesUser,
            ];
        }

        return $this->render('front/equipe/my_participations.html.twig', [
            'participations' => $participations,
            'eventsForCalendar' => $eventsForCalendar,
        ]);
    }

    #[Route('/mes-equipes', name: 'equipe_my_teams', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myTeams(EquipeRepository $equipeRepository): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }
        $equipesOwned = $equipeRepository->findBy(['owner' => $user]);

        return $this->render('front/equipe/my_teams.html.twig', [
            'equipes' => $equipesOwned,
        ]);
    }

    #[Route('/equipes/new', name: 'equipe_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipe = new Equipe();
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }
        $equipe->setOwner($user);

        $form = $this->createForm(EquipeType::class, $equipe, ['include_members' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // S'assurer que le propriétaire est dans les membres
            if (!$equipe->getMembers()->contains($user)) {
                $equipe->addMember($user);
            }

            $entityManager->persist($equipe);
            $entityManager->flush();

            $this->addFlash('success', 'Équipe créée avec succès !');
            return $this->redirectToRoute('front_equipe_my_teams', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/equipe/new.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
        ]);
    }

    #[Route('/equipes/{idEquipe}', name: 'equipe_show', methods: ['GET'], requirements: ['idEquipe' => '\d+'])]
    public function show(int $idEquipe, EquipeRepository $equipeRepository): Response
    {
        $equipe = $equipeRepository->find($idEquipe);

        if (!$equipe) {
            throw $this->createNotFoundException('Équipe non trouvée.');
        }
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }
        $isOwner =  $equipe->getOwner() === $user;

        return $this->render('front/equipe/show.html.twig', [
            'equipe' => $equipe,
            'isOwner' => $isOwner,
        ]);
    }

    #[Route('/equipes/{idEquipe}/edit', name: 'equipe_edit', methods: ['GET', 'POST'], requirements: ['idEquipe' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, int $idEquipe, EquipeRepository $equipeRepository, EntityManagerInterface $entityManager): Response
    {
        $equipe = $equipeRepository->find($idEquipe);

        if (!$equipe) {
            throw $this->createNotFoundException('Équipe non trouvée.');
        }

        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }

        // Vérifier que l'utilisateur est le propriétaire
        if ($equipe->getOwner() !== $user) {
            $this->addFlash('error', 'Vous n\'avez pas le droit de modifier cette équipe.');
            return $this->redirectToRoute('front_equipe_show', ['idEquipe' => $equipe->getIdEquipe()]);
        }

        $form = $this->createForm(EquipeType::class, $equipe, ['include_members' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // S'assurer que le propriétaire reste dans les membres
            if (!$equipe->getMembers()->contains($user)) {
                $equipe->addMember($user);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Équipe modifiée avec succès !');
            return $this->redirectToRoute('front_equipe_show', ['idEquipe' => $equipe->getIdEquipe()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
        ]);
    }

    #[Route('/equipes/{idEquipe}/delete', name: 'equipe_delete', methods: ['POST'], requirements: ['idEquipe' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, int $idEquipe, EquipeRepository $equipeRepository, EntityManagerInterface $entityManager): Response
    {
        $equipe = $equipeRepository->find($idEquipe);

        if (!$equipe) {
            throw $this->createNotFoundException('Équipe non trouvée.');
        }

        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }

        // Vérifier que l'utilisateur est le propriétaire
        if ($equipe->getOwner() !== $user) {
            $this->addFlash('error', 'Vous n\'avez pas le droit de supprimer cette équipe.');
            return $this->redirectToRoute('front_equipe_show', ['idEquipe' => $equipe->getIdEquipe()]);
        }

        $token = $request->request->getString('_token');
        if ($token && $this->isCsrfTokenValid('delete' . $equipe->getIdEquipe(), $token)) {
            $entityManager->remove($equipe);
            $entityManager->flush();
            $this->addFlash('success', 'Équipe supprimée avec succès !');
        }

        return $this->redirectToRoute('front_equipe_my_teams', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/equipes/{idEquipe}/remove-member/{userId}', name: 'equipe_remove_member', methods: ['POST'], requirements: ['idEquipe' => '\d+', 'userId' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function removeMember(Request $request, int $idEquipe, int $userId, EquipeRepository $equipeRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $equipe = $equipeRepository->find($idEquipe);

        if (!$equipe) {
            throw $this->createNotFoundException('Équipe non trouvée.');
        }

        $member = $userRepository->find($userId);

        if (!$member) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }

        // Vérifier que l'utilisateur est le propriétaire
        if ($equipe->getOwner() !== $user) {
            $this->addFlash('error', 'Vous n\'avez pas le droit de modifier cette équipe.');
            return $this->redirectToRoute('front_equipe_show', ['idEquipe' => $equipe->getIdEquipe()]);
        }

        // Ne pas permettre de retirer le propriétaire
        if ($member === $equipe->getOwner()) {
            $this->addFlash('error', 'Vous ne pouvez pas retirer le propriétaire de l\'équipe.');
            return $this->redirectToRoute('front_equipe_edit', ['idEquipe' => $equipe->getIdEquipe()]);
        }

        $token = $request->request->getString('_token');
        if ($token && $this->isCsrfTokenValid('remove_member' . $equipe->getIdEquipe() . $member->getId(), $token)) {
            $equipe->removeMember($member);
            $entityManager->flush();
            $this->addFlash('success', 'Membre retiré avec succès !');
        }

        return $this->redirectToRoute('front_equipe_edit', ['idEquipe' => $equipe->getIdEquipe()], Response::HTTP_SEE_OTHER);
    }
}
