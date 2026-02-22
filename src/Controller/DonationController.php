<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Form\DonationType;
use App\Repository\DonationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\StreamRepository;
use App\Entity\Stream;
use App\Repository\UserRepository;

#[Route('/donation')]
final class DonationController extends AbstractController
{
   #[Route('/donation/stream/{id}/donations', name:"app_donation_index")]
public function index(Stream $stream, DonationRepository $donationRepo): Response
{
    $donations = $donationRepo->findBy(['stream' => $stream]);

    return $this->render('donation/index.html.twig', [
        'donations' => $donations,
        'stream' => $stream, 
    ]);
}



    #[Route('/donation/new/{streamId}', name: 'donation_new')]
public function new(Request $request, EntityManagerInterface $em, int $streamId, StreamRepository $streamRepo, UserRepository $userRepository): Response
{
    $stream = $streamRepo->find($streamId);
    if (!$stream) {
        throw $this->createNotFoundException('Stream introuvable');
    }

    $donation = new Donation();
    $donation->setStream($stream); // Liaison avec le stream

    $form = $this->createForm(DonationType::class, $donation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $user = $userRepository->find(1);   // 👤 donneur
        $donation->setUser($user);
        $donation->setStream($stream);  
        
        $em->persist($donation);
        $em->flush();

        $this->addFlash('success', 'Merci pour votre donation !');
        return $this->redirectToRoute('stream_show', ['id' => $streamId]);
    }


    return $this->render('donation/new.html.twig', [
        'donation' => $donation,
        'form' => $form->createView(),
        'stream' => $stream
    ]);
}


    #[Route('/{id}', name: 'app_donation_show', methods: ['GET'])]
    public function show(Donation $donation): Response
    {
        return $this->render('donation/show.html.twig', [
            'donation' => $donation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_donation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Donation $donation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DonationType::class, $donation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_donation_index', ["id" => $donation->getStream()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('donation/edit.html.twig', [
            'donation' => $donation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_donation_delete', methods: ['POST'])]
    public function delete(Request $request, Donation $donation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$donation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($donation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_donation_index', ["id" => $donation->getStream()->getId()], Response::HTTP_SEE_OTHER);
    }
    #[Route('/stream/{streamId}/donate/{emoji}', name:'donation_emoji')]
public function donateByEmoji(int $streamId, string $emoji, EntityManagerInterface $em, StreamRepository $streamRepo,UserRepository $userRepository): Response
{
    $stream = $streamRepo->find($streamId);
    if (!$stream) {
        throw $this->createNotFoundException('Stream introuvable');
    }

    // Définir les montants
    $emojiAmounts = [
        '🍩' => 1,
        '🍕' => 5,
        '💎' => 10,
        '🚀' => 50,
    ];

    if (!isset($emojiAmounts[$emoji])) {
        throw $this->createNotFoundException('Emoji invalide');
    }

    $donation = new Donation();
    $donation->setStream($stream);
    $user = $userRepository->find(1);
    $donation->setUser($user);
    $donation->setDonorName('Anonymous'); // Ou ajouter utilisateur connecté
    $donation->setAmount($emojiAmounts[$emoji]);

    $em->persist($donation);
    $em->flush();

    $this->addFlash('success', "Merci pour votre donation $emoji (€{$emojiAmounts[$emoji]}) !");

    return $this->redirectToRoute('stream_show', ['id' => $streamId]);
}
#[Route('/admin/donation', name:'admin_donation_index')]
// LISTE DES STREAMS (ADMIN)
#[Route('/admin/donations', name: 'admin_donation_streams')]
public function adminStreams(Request $request,StreamRepository $streamRepository): Response

    {
    $search = $request->query->get('q');

    $streams = $streamRepository->searchByTitleOrId($search);

    return $this->render('admin/donation/streams.html.twig', [
        'streams' => $streams,
        'search' => $search,
    ]);
}
// DONATIONS D’UN STREAM (ADMIN)
#[Route('/admin/donations/stream/{id}', name: 'admin_donation_by_stream')]
public function adminDonationsByStream(
    Stream $stream,
    DonationRepository $donationRepository
): Response {
    return $this->render('admin/donation/index.html.twig', [
        'stream' => $stream,
        'donations' => $donationRepository->findBy(
            ['stream' => $stream],
            ['createdAt' => 'DESC']
        ),
    ]);
}

}
