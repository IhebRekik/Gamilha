<?php

namespace App\Controller;

use App\Entity\Abonnement;
use App\Entity\HistoriquePaiement;
use App\Entity\UserAbonnement;
use App\Form\UserAbonnementType;
use App\Repository\UserAbonnementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserAbonnementController extends AbstractController
{
    #[Route('/admin/abonnements', name: 'app_user_abonnement_index', methods: ['GET'])]
    public function index(UserAbonnementRepository $userAbonnementRepository): Response
    {
        return $this->render('admin/user_abonnement/index.html.twig', [
            'user_abonnements' => $userAbonnementRepository->findAll(),
        ]);
    }

    #[Route('/admin/user/abonnement/new', name: 'app_user_abonnement_new', methods: ['GET'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $userAbonnement = new UserAbonnement();
        $form = $this->createForm(UserAbonnementType::class, $userAbonnement);
        $form->handleRequest($request);



        return $this->render('admin/user_abonnement/new.html.twig', [
            'user_abonnement' => $userAbonnement,
            'form' => $form,
        ]);
    }
    #[Route('/payment/success', name: 'app_payment_success')]
    public function paymentSuccess(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): Response {

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $sessionId = $request->query->get('session_id');

        $session = Session::retrieve($sessionId);

        $userId = $session->metadata->user_id;
        $abonnementId = $session->metadata->abonnement_id;

        $user = $userRepository->find($userId);
        $abonnement = $em->getRepository(Abonnement::class)->find($abonnementId);

        $userAbonnement = new UserAbonnement();
        $userAbonnement->setUser($user);
        $userAbonnement->setAbonnement($abonnement);
        $userAbonnement->setDateDebut(new \DateTime());
        $userAbonnement->setDateFin((new \DateTime())->modify('+' . $abonnement->getDuree() . ' months'));

        $em->persist($userAbonnement);
        $em->flush();
        $paiement = new HistoriquePaiement();
        $paiement->setUser($user);
        $paiement->setAbonnement($abonnement);
        $paiement->setMontant($abonnement->getPrix());
        $paiement->setCreatedAt(new \DateTime());
        $em->persist($paiement);
        $em->flush();

        return $this->redirectToRoute('app_homepage');
    }
    #[Route('/payment/cancel', name: 'app_payment_cancel')]
    public function paymentCancel(): Response
    {
        $this->addFlash('danger', 'Paiement annulé ❌');

        return $this->redirectToRoute('app_homepage');
    }
    #[Route('/user/abonnement/new/{id}', name: 'app_user_abonnement_new', methods: ['POST'])]
    public function subscribe(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserAbonnementRepository $userAbonnementRepository,
        int $id
    ): Response {

        $userEmail = $request->cookies->get('user_email');
        if (!$userEmail) {
            return new Response('Utilisateur non connecté', Response::HTTP_UNAUTHORIZED);
        }

        $user = $userRepository->findOneBy(['email' => $userEmail]);
        if (!$user) {
            return new Response('Utilisateur non trouvé', Response::HTTP_NOT_FOUND);
        }

        $abonnement = $entityManager->getRepository(Abonnement::class)->find($id);
        if (!$abonnement) {
            return new Response('Abonnement non trouvé', Response::HTTP_NOT_FOUND);
        }
        $userAbonnement1 = $userAbonnementRepository->findOneBy(['user' => $user, 'abonnement' => $abonnement]);
        if ($userAbonnement1) {
            return $this->render('admin/user_abonnement/erreur.html.twig', [
                'message' => 'Vous êtes déjà abonné à ce plan.',
            ]);
        }
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur', // ou usd
                    'product_data' => [
                        'name' => $abonnement->getType(),
                    ],
                    'unit_amount' => $abonnement->getPrix() / 3.2 * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',

            // On passe user + abonnement dans metadata
            'metadata' => [
                'user_id' => $user->getId(),
                'abonnement_id' => $abonnement->getId(),
            ],

            'success_url' => $this->generateUrl(
                'app_payment_success',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ) . '?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' => $this->generateUrl(
                'app_homepage',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return $this->redirect($session->url);
    }
    #[Route('/admin/{id}', name: 'app_user_abonnement_show', methods: ['GET'])]
    public function show(UserAbonnement $userAbonnement): Response
    {
        return $this->render('admin/user_abonnement/show.html.twig', [
            'user_abonnement' => $userAbonnement,
        ]);
    }

    #[Route('/admin/user/abonnement/{id}/edit', name: 'app_user_abonnement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserAbonnement $userAbonnement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserAbonnementType::class, $userAbonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_abonnement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user_abonnement/edit.html.twig', [
            'user_abonnement' => $userAbonnement,
            'form' => $form,
        ]);
    }

    #[Route('/admin/user/abonnement/{id}', name: 'app_user_abonnement_delete', methods: ['POST'])]
    public function delete(Request $request, UserAbonnement $userAbonnement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $userAbonnement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($userAbonnement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_abonnement_index', [], Response::HTTP_SEE_OTHER);
    }
}
