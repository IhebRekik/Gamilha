<?php

// src/Controller/Admin/DashboardController.php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route ('/admin')]
class DashboardController extends AbstractController
{

    public function setCookie(): Response
    {
        $response = new Response('User ID saved in cookie');

        $userId = 4; // example user id

        $cookie = Cookie::create('user_id')
            ->withValue($userId)
            ->withExpires(strtotime('+1 day'));

        $response->headers->setCookie($cookie);

        return $response;
    }


   
#[Route('/dashboard', name: 'admin_dashboard')]
public function index(Request $request, ChartBuilderInterface $chartBuilder): Response
{
    $userId = 4; // example value (replace with real user id)

    // Charts
    $userChart = $chartBuilder->createChart(Chart::TYPE_LINE);
    $revenueChart = $chartBuilder->createChart(Chart::TYPE_BAR);
    $gamesChart = $chartBuilder->createChart(Chart::TYPE_BAR);
    $subChart = $chartBuilder->createChart(Chart::TYPE_PIE);

    $response = $this->render('admin/dashboard/index.html.twig', [
        'userChart'    => $userChart,
        'revenueChart' => $revenueChart,
        'gamesChart'   => $gamesChart,
        'subChart'     => $subChart,
    ]);

    // Create cookie
    $cookie = Cookie::create('user_id')
        ->withValue($userId)
        ->withExpires(strtotime('+1 day'))
        ->withHttpOnly(true);

    $response->headers->setCookie($cookie);

    return $response;
}
}
