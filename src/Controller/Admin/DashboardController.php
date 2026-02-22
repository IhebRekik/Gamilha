<?php

// src/Controller/Admin/DashboardController.php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use  App\Service\StreamPredictionService;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

#[Route ('/admin')]
class DashboardController extends AbstractController
{


#[Route('/dashboard', name: 'admin_dashboard')]
public function index(Request $request, ChartBuilderInterface $chartBuilder,StreamPredictionService $predictionService, UserRepository $userRepository,): Response
{

    // Charts
    $userChart = $chartBuilder->createChart(Chart::TYPE_LINE);
    $revenueChart = $chartBuilder->createChart(Chart::TYPE_BAR);
    $gamesChart = $chartBuilder->createChart(Chart::TYPE_BAR);
    $subChart = $chartBuilder->createChart(Chart::TYPE_PIE);
     // Récupérer tous les streamers
    $users = $userRepository->findAll();

    $predictions = [];

    foreach ($users as $user) {

        
       if (!in_array('ROLE_USER', $user->getRoles())) {
    continue;
}

        $predictions[] = [
            'user' => $user,
            'data' => $predictionService->predictStreams($user, 30)
        ];
    }

    $response = $this->render('admin/dashboard/index.html.twig', [
        'userChart'    => $userChart,
        'revenueChart' => $revenueChart,
        'gamesChart'   => $gamesChart,
        'subChart'     => $subChart,
        'predictionData' => $predictions,
        
    ]);

    

    return $response;
}



    

}
