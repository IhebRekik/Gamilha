<?php

// src/Controller/Admin/DashboardController.php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\UserRepository;
use App\Repository\StreamRepository;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(ChartBuilderInterface $chartBuilder): Response
    {
        // Graphiques statiques initiaux (seront mis à jour via LiveComponent)
        $userChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $revenueChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $gamesChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $subChart = $chartBuilder->createChart(Chart::TYPE_PIE);

        return $this->render('admin/dashboard/index.html.twig', [
            'userChart'    => $userChart,
            'revenueChart' => $revenueChart,
            'gamesChart'   => $gamesChart,
            'subChart'     => $subChart,
        ]);
    }
}