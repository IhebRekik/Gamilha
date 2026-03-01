<?php

namespace App\Controller;

use App\Entity\Stream;
use App\Form\StreamType;
use App\Repository\AbonnementRepository;
use App\Repository\StreamRepository;
use App\Repository\UserAbonnementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Service\ApiVideoService;
use App\Service\AblyService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\User;

final class StreamController extends AbstractController
{
    #[Route('/stream', name: 'stream_index', methods: ['GET','POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        StreamRepository $repo,
        UserRepository $userRepository,
        ApiVideoService $apiVideo,
       AblyService $ablyService, UserAbonnementRepository $userAbonnementRepository
    ): Response
    {
        $stream = new Stream();
        $form = $this->createForm(StreamType::class, $stream);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $userRepository->findOneBy(['email' => $request->cookies->get('user_email')]); // Récupérer un utilisateur (ex: ID 1)

            $abonnementsActifs = $userAbonnementRepository->createQueryBuilder('ua')
                ->where('ua.user = :user')
                ->andWhere('ua.dateFin > :now')
                ->setParameter('user', $user)
                ->setParameter('now', new \DateTime())
                ->getQuery()
                ->getResult();
            $hasStreaming = false;

            foreach ($abonnementsActifs as $userAbonnement) {
                $options = $userAbonnement->getAbonnement()->getOptions();

                if ($options && in_array('stream', $options)) {
                    $hasStreaming = true;
                    break; // inutile de continuer
                }
            }

            if (!$hasStreaming) {
                $this->addFlash('danger', 'Votre abonnement ne permet pas le streaming.');
                return $this->redirectToRoute('app_abonnementuser_index');
            }

            

            $stream->setUser($user);
            $stream->setStatus('live');
            $stream->setViewers(0);

            // Création du live via api.video
            $liveData = $apiVideo->createLiveStream($stream->getTitle());

            $stream->setApiVideoId($liveData['apiVideoId']);
            $stream->setStreamKey($liveData['streamKey']);
            $stream->setRtmpServer($liveData['rtmpServer']);
            $stream->setUrl($liveData['playerUrl']);
            $stream->setStatus($liveData['broadcasting'] ? 'live' : 'offline');
            $stream->setIsLive($liveData['broadcasting']);

            $em->persist($stream);
            $em->flush();

            // 🔔 Notification Centrifugo (exclut le streamer lui-même côté frontend)
            $ablyService->publish('streams:new', [
                'type'        => 'new_stream',
                'streamId'    => $stream->getId(),
                'title'       => $stream->getTitle(),
                'streamerName'=> $user->getName(),
                'streamerId'  => $user->getId(),
                'game'        => $stream->getGame(),
                'url'         => $this->generateUrl('stream_show', ['id' => $stream->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                
            ]);

            $this->addFlash('success', 'Stream lancé ! Les autres utilisateurs sont notifiés.');


            return $this->redirectToRoute('stream_show', ['id' => $stream->getId()]);
        }

        // Filtres
        $query = $request->query->get('q', '');
        $game = $request->query->get('game', '');
        $sort = $request->query->get('sort', '');

        $qb = $repo->createQueryBuilder('s');

        if ($query) {
            $qb->andWhere('s.title LIKE :query OR s.description LIKE :query')
               ->setParameter('query', "%$query%");
        }

        if ($game) {
            $qb->andWhere('s.game = :game')
               ->setParameter('game', $game);
        }

        if ($sort === 'viewers') {
            $qb->orderBy('s.viewers', 'DESC');
        } else {
            $qb->orderBy('s.createdAt', 'DESC');
        }

        $streams = $qb->getQuery()->getResult();

        return $this->render('stream/index.html.twig', [
            'streams' => $streams,
            'form'    => $form->createView(),
            'query'   => $query,
            'game'    => $game,
            'sort'    => $sort,
        ]);
    }

    #[Route('/stream/{id}', name: 'stream_show', methods: ['GET'])]
    public function show(Stream $stream): Response
    {
        return $this->render('stream/show.html.twig', [
            'stream' => $stream,
        ]);
    }

    public function view(string $streamKey): Response
    {
        return $this->render('stream/show.html.twig', [
            'streamKey' => $streamKey,
        ]);
    }
    #[Route('/test-prediction/{id}', name: 'test_prediction')]
public function testPrediction(
    User $user,
    \App\Service\StreamPredictionService $predictionService
) {
    $result = $predictionService->predictStreams($user, 30);

    return $this->render('prediction/show.html.twig', [
    'data' => $result
]);
}
}
