<?php

namespace App\Controller\Front;

use App\Entity\Bracket;
use App\Entity\Evenement;
use App\Entity\GameMatch;
use App\Entity\User;
use App\Form\FrontEvenementType;
use App\Form\GameMatchEditType;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Repository\UserAbonnementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('', name: 'front_')]
class EvenementController extends AbstractController
{
    #[Route('/evenements', name: 'evenement_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository): Response
    {
        $evenements = $evenementRepository->findBy(
            [],
            ['dateDebut' => 'DESC']
        );

        return $this->render('front/evenement/index.html.twig', [
            'evenements' => $evenements,
        ]);
    }

    #[Route('/evenements/mes-evenements', name: 'evenement_my_events', methods: ['GET'])]
    public function myEvents(EvenementRepository $evenementRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }
        $evenements = $evenementRepository->findByCreatedBy($user);

        return $this->render('front/evenement/my_events.html.twig', [
            'evenements' => $evenements,
        ]);
    }

    #[Route('/evenements/nouveau', name: 'evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager , UserRepository $userRepository, UserAbonnementRepository $userAbonnementRepository): Response
    {
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

                if ($options && in_array('evenement', $options)) {
                    $hasStreaming = true;
                    break; // inutile de continuer
                }
            }

            if (!$hasStreaming) {
                $this->addFlash('danger', 'Votre abonnement ne permet pas le streaming.');
                return $this->redirectToRoute('app_abonnementuser_index');
            }
        $this->denyAccessUnlessGranted('ROLE_USER');

        $evenement = new Evenement();
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }
        $evenement->setCreatedBy($user);
        $evenement->setStatut('prévu');

        $form = $this->createForm(FrontEvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeBracket = $form->get('typeBracket')->getData();
            $equipes = $evenement->getEquipesParticipantes();
            $n = $equipes->count();
            $nombreTours = $n >= 2 ? (int) max(1, ceil(log($n, 2))) : 1;

            $bracket = new Bracket();
            $bracket->setTypeBracket($typeBracket);
            $bracket->setNombreTours($nombreTours);
            $bracket->setStatut('en attente');
            $bracket->setEvenement($evenement);
            $evenement->addBracket($bracket);

            $entityManager->persist($evenement);
            $entityManager->persist($bracket);
            $entityManager->flush();

            $this->addFlash('success', 'Événement créé. Effectuez le tirage au sort pour générer les confrontations.');
            return $this->redirectToRoute('front_evenement_tirage', ['idEvenement' => $evenement->getIdEvenement()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/evenements/{idEvenement}/tirage', name: 'evenement_tirage', methods: ['GET', 'POST'])]
    public function tirage(Request $request, #[MapEntity(mapping: ['idEvenement' => 'idEvenement'])] Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }
        if ($evenement->getCreatedBy() !== $user) {
            throw $this->createAccessDeniedException('Seul le créateur de l\'événement peut effectuer le tirage.');
        }

        $bracket = $evenement->getBrackets()->first();
        if (!$bracket || $bracket->getMatchs()->count() > 0) {
            if ($bracket && $bracket->getMatchs()->count() > 0) {
                $this->addFlash('info', 'Le tirage a déjà été effectué. Vous pouvez modifier les matchs.');
                return $this->redirectToRoute('front_evenement_edit_matches', ['idEvenement' => $evenement->getIdEvenement()], Response::HTTP_SEE_OTHER);
            }
            $this->addFlash('danger', 'Aucun bracket trouvé ou événement invalide.');
            return $this->redirectToRoute('front_evenement_show', ['idEvenement' => $evenement->getIdEvenement()], Response::HTTP_SEE_OTHER);
        }

        if ($request->isMethod('POST')) {
            $equipes = $evenement->getEquipesParticipantes()->toArray();
            shuffle($equipes);
            $n = count($equipes);
            if ($n < 2) {
                $this->addFlash('danger', 'Il faut au moins 2 équipes participantes.');
                return $this->redirectToRoute('front_evenement_tirage', ['idEvenement' => $evenement->getIdEvenement()], Response::HTTP_SEE_OTHER);
            }

            $nombreTours = (int) max(1, ceil(log($n, 2)));
            $bracket->setNombreTours($nombreTours);

            $matchesRound1 = (int) ceil($n / 2);
            $idx = 0;
            for ($i = 0; $i < $matchesRound1; $i++) {
                $match = new GameMatch();
                $match->setBracket($bracket);
                $match->setTour(1);
                $match->setStatut('à venir');
                $match->setScoreEquipeA(0);
                $match->setScoreEquipeB(0);
                $match->setEquipeA($equipes[$idx] ?? null);
                $match->setEquipeB($equipes[$idx + 1] ?? null);
                $idx += 2;
                if ($evenement->getDateDebut()) {
                    $date = \DateTime::createFromInterface($evenement->getDateDebut());
                    $date->setTime(14, 0);
                    $match->setDateMatch($date);
                }
                $bracket->addMatch($match);
                $entityManager->persist($match);
            }

            $prevCount = $matchesRound1;
            for ($tour = 2; $tour <= $nombreTours; $tour++) {
                $matchCount = (int) ceil($prevCount / 2);
                for ($i = 0; $i < $matchCount; $i++) {
                    $match = new GameMatch();
                    $match->setBracket($bracket);
                    $match->setTour($tour);
                    $match->setStatut('à venir');
                    $match->setScoreEquipeA(0);
                    $match->setScoreEquipeB(0);
                    $match->setEquipeA(null);
                    $match->setEquipeB(null);
                    if ($evenement->getDateDebut()) {
                        $date = \DateTime::createFromInterface($evenement->getDateDebut());
                        $date->modify('+' . ($tour - 1) . ' days')->setTime(14, 0);
                        $match->setDateMatch($date);
                    }
                    $bracket->addMatch($match);
                    $entityManager->persist($match);
                }
                $prevCount = $matchCount;
            }

            $entityManager->flush();
            $this->addFlash('success', 'Tirage au sort effectué. Saisissez les détails (dates) des matchs.');
            return $this->redirectToRoute('front_evenement_edit_matches', ['idEvenement' => $evenement->getIdEvenement()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/evenement/tirage.html.twig', [
            'evenement' => $evenement,
            'bracket' => $bracket,
        ]);
    }

    #[Route('/evenements/{idEvenement}/modifier-matchs', name: 'evenement_edit_matches', methods: ['GET', 'POST'])]
    public function editMatches(Request $request, #[MapEntity(mapping: ['idEvenement' => 'idEvenement'])] Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }
        if ($evenement->getCreatedBy() !== $user) {
            throw $this->createAccessDeniedException('Seul le créateur peut modifier les matchs.');
        }

        $bracket = $evenement->getBrackets()->first();
        if (!$bracket) {
            $this->addFlash('danger', 'Aucun bracket pour cet événement.');
            return $this->redirectToRoute('front_evenement_show', ['idEvenement' => $evenement->getIdEvenement()], Response::HTTP_SEE_OTHER);
        }

        $matchs = $bracket->getMatchs()->toArray();
        usort($matchs, fn(GameMatch $a, GameMatch $b) => [$a->getTour(), $a->getIdMatch()] <=> [$b->getTour(), $b->getIdMatch()]);

        $form = $this->createFormBuilder(['matchs' => $matchs])
            ->add('matchs', CollectionType::class, [
                'entry_type' => GameMatchEditType::class,
                'entry_options' => ['label' => false],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Détails des matchs enregistrés.');
            return $this->redirectToRoute('front_evenement_show', ['idEvenement' => $evenement->getIdEvenement()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/evenement/edit_matches.html.twig', [
            'evenement' => $evenement,
            'bracket' => $bracket,
            'matchs' => $matchs,
            'form' => $form,
        ]);
    }

    #[Route('/evenements/{idEvenement}', name: 'evenement_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['idEvenement' => 'idEvenement'])] Evenement $evenement, EvenementRepository $evenementRepository): Response
    {
        $bracketsWithRounds = [];
        foreach ($evenement->getBrackets() as $bracket) {
            $matchsByTour = [];
            foreach ($bracket->getMatchs() as $match) {
                $tour = $match->getTour();
                if (!isset($matchsByTour[$tour])) {
                    $matchsByTour[$tour] = [];
                }
                $matchsByTour[$tour][] = $match;
            }
            ksort($matchsByTour);
            foreach ($matchsByTour as $tour => $matchs) {
                usort($matchsByTour[$tour], fn($a, $b) => $a->getIdMatch() <=> $b->getIdMatch());
            }
            $bracketsWithRounds[] = [
                'bracket' => $bracket,
                'matchsByTour' => $matchsByTour,
                'rounds' => array_keys($matchsByTour),
            ];
        }

        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }
        $isOwner =  $evenement->getCreatedBy() === $user;

        // Récupérer les événements similaires basés sur la description
        $similarEvents = $evenementRepository->findSimilarEvents($evenement, 4);

        return $this->render('front/evenement/show.html.twig', [
            'evenement' => $evenement,
            'bracketsWithRounds' => $bracketsWithRounds,
            'is_owner' => $isOwner,
            'similarEvents' => $similarEvents,
        ]);
    }

    #[Route('/evenements/{idEvenement}/modifier', name: 'evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[MapEntity(mapping: ['idEvenement' => 'idEvenement'])] Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }
        if ($evenement->getCreatedBy() !== $user) {
            throw $this->createAccessDeniedException('Seul le créateur peut modifier l\'événement.');
        }

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Événement mis à jour.');
            return $this->redirectToRoute('front_evenement_show', ['idEvenement' => $evenement->getIdEvenement()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/evenements/{idEvenement}/supprimer', name: 'evenement_delete', methods: ['POST'])]
    public function delete(Request $request, #[MapEntity(mapping: ['idEvenement' => 'idEvenement'])] Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }
        if ($evenement->getCreatedBy() !== $user) {
            throw $this->createAccessDeniedException('Seul le créateur peut supprimer l\'événement.');
        }

        $token = $request->request->getString('_token');
        if ($token && $this->isCsrfTokenValid('delete' . $evenement->getIdEvenement(), $token)) {
            $entityManager->remove($evenement);
            $entityManager->flush();
            $this->addFlash('success', 'Événement supprimé.');
        }

        return $this->redirectToRoute('front_evenement_my_events', [], Response::HTTP_SEE_OTHER);
    }
}
