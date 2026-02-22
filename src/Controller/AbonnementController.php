<?php

namespace App\Controller;

use App\Entity\Abonnement;
use App\Form\AbonnementType;
use App\Repository\AbonnementRepository;
use App\Repository\UserAbonnementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/abonnement')]
final class AbonnementController extends AbstractController
{
    #[Route(name: 'app_abonnement_index', methods: ['GET'])]
    public function index(AbonnementRepository $abonnementRepository): Response
    {
        return $this->render('admin/abonnement/index.html.twig', [
            'abonnements' => $abonnementRepository->findAll(),
            'types' => $abonnementRepository->findDistinctTypes()
        ]);
    }

    #[Route('/new', name: 'app_abonnement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $abonnement = new Abonnement();
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avantagesText = $form->get('avantages')->getData();
            $abonnement->setAvantages(explode(",", trim($avantagesText)));
            $entityManager->persist($abonnement);
            $entityManager->flush();

            return $this->redirectToRoute('app_abonnement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/abonnement/new.html.twig', [
            'abonnement' => $abonnement,
            'form' => $form,
            'edit' => false
        ]);
    }

    #[Route('/{id}', name: 'app_abonnement_show', methods: ['GET'])]
    public function show(int $id, AbonnementRepository $repo): Response
    {
        $abonnement = $repo->find($id);

        if (!$abonnement) {
            throw $this->createNotFoundException('Abonnement introuvable.');
        }

        return $this->render('admin/abonnement/show.html.twig', [
            'abonnement' => $abonnement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_abonnement_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, AbonnementRepository $repo, EntityManagerInterface $entityManager): Response
    {
        $abonnement = $repo->find($id);

        if (!$abonnement) {
            throw $this->createNotFoundException('Abonnement introuvable.');
        }

        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_abonnement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/abonnement/edit.html.twig', [
            'abonnement' => $abonnement,
            'form' => $form,
            'edit' => true
        ]);
    }

    #[Route('/{id}', name: 'app_abonnement_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, AbonnementRepository $repo, EntityManagerInterface $entityManager): Response
    {
        $abonnement = $repo->find($id);

        if (!$abonnement) {
            throw $this->createNotFoundException('Abonnement introuvable.');
        }

        if ($this->isCsrfTokenValid('delete' . $abonnement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($abonnement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_abonnement_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route("/type/get", name: 'app_abonnementuser_index', methods: ['GET'])]
    public function user_index(AbonnementRepository $abonnementRepository, UserAbonnementRepository $userAbonnementRepository, UserRepository $userRepository, Request $request): Response
    {
        $user = $userRepository->findOneBy(['email' => $request->cookies->get('user_email')]);
        $abonnements = $abonnementRepository->findAll();
        $abonnementsActif = [];
        $abonnementsUser = [];
        foreach ($abonnements as $abonnement) {
            $abonnementsActif = $userAbonnementRepository->createQueryBuilder('ua')
                ->where('ua.user = :user')
                ->andWhere('ua.dateFin > :now')
                ->andWhere('ua.abonnement = :abonnement')
                ->setParameter('user', $user)
                ->setParameter('now', new \DateTime())
                ->setParameter('abonnement', $abonnement)
                ->getQuery()
                ->getResult();
            if (!empty($abonnementsActif)) {
                $abonnementsUser[] = $abonnement->getId();
            }
        }


        return $this->render('admin/user_abonnement/user_index.html.twig', [
            'abonnements' => $abonnementRepository->findAll(),
            'userAbonnements' => $abonnementsUser
        ]);
    }
}
