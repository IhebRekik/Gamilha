<?php

namespace App\Controller;

use App\Repository\HistoriquePaiementRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HistoriquePaiementController extends AbstractController
{
    #[Route('/admin/historique/paiement', name: 'admin_historique_paiement_index')]
    public function index(HistoriquePaiementRepository $historiquePaiementRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $pagination = $paginator->paginate(
    $historiquePaiementRepository->createQueryBuilder('h')
        ->orderBy('h.createdAt', 'DESC'),
    $request->query->getInt('page', 1),
    10
);

return $this->render('historique_paiement/index.html.twig', [
    'pagination' => $pagination,
]);
        
    }
}
