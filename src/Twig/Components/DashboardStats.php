<?php

namespace App\Twig\Components;

use App\Repository\EvenementRepository;
use App\Repository\UserRepository;
use App\Repository\HistoriquePaiementRepository;
use App\Repository\StreamRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('dashboard_stats')]
class DashboardStats
{
    public function __construct(
        private UserRepository $userRepository,
        private HistoriquePaiementRepository $historiqueRepository,
        private EvenementRepository $tournamentRepository,
        private StreamRepository $streamRepository

    ) {}
    public function getGamesStats(): array
    {
        $stats = $this->tournamentRepository->countByGame();

        return [
            'labels' => array_column($stats, 'jeu'),
            'data'   => array_column($stats, 'total')
        ];
    }

    // ====== CARTES ======

    public function getUsersCount(): int
    {
        return $this->userRepository->getUsersCount();
    }

    public function getMonthlyRevenue(): float
    {
        return $this->historiqueRepository->getMonthlyRevenue();
    }

    // ====== CHART USERS ======
    public function getUserGrowth(): array
{
    $users = $this->userRepository->getUsersCreated();

    $months = [];

    foreach ($users as $user) {

        /** @var \DateTimeInterface $date */
        $date = $user['createdAt'];

        $monthKey = $date->format('Y-m');      // 2026-02
        $monthLabel = $date->format('M Y');    // Feb 2026

        if (!isset($months[$monthKey])) {
            $months[$monthKey] = [
                'label' => $monthLabel,
                'count' => 0
            ];
        }

        $months[$monthKey]['count']++;
    }

    ksort($months); // ordre chronologique

    return [
        'labels' => array_column($months, 'label'),
        'data'   => array_column($months, 'count')
    ];
}

    // ====== CHART REVENUE ======

   public function getRevenueGrowth(): array
{
    $payments = $this->historiqueRepository->getAllPayments();

    $months = [];

    foreach ($payments as $payment) {
        $monthKey = $payment['createdAt']->format('Y-m');
        $monthLabel = $payment['createdAt']->format('M Y');

        if (!isset($months[$monthKey])) {
            $months[$monthKey] = [
                'label' => $monthLabel,
                'total' => 0
            ];
        }

        $months[$monthKey]['total'] += (float) $payment['montant'];
    }

    ksort($months);

    return [
        'labels' => array_column($months, 'label'),
        'data'   => array_column($months, 'total')
    ];
}
    // ====== CHART SUBSCRIPTIONS ======

    public function getSubscriptionStats(): array
    {
        $stats = $this->historiqueRepository->countByType();

        return [
            'labels' => array_column($stats, 'type'),
            'data'   => array_column($stats, 'total')
        ];
    }
    private function calculateEvolution($thisMonth, $lastMonth): float
{
    if ($lastMonth == 0) {
        return $thisMonth > 0 ? 100 : 0;
    }

    return (($thisMonth - $lastMonth) / $lastMonth) * 100;
}
    public function getUsersEvolution(): float
{
    $startThisMonth = new \DateTime('first day of this month');
    $startLastMonth = new \DateTime('first day of last month');
    $endLastMonth = new \DateTime('last day of last month');

    $thisMonth = $this->userRepository->createQueryBuilder('u')
        ->select('COUNT(u)')
        ->where('u.createdAt >= :start')
        ->setParameter('start', $startThisMonth)
        ->getQuery()
        ->getSingleScalarResult();

    $lastMonth = $this->userRepository->createQueryBuilder('u')
        ->select('COUNT(u)')
        ->where('u.createdAt BETWEEN :start AND :end')
        ->setParameter('start', $startLastMonth)
        ->setParameter('end', $endLastMonth)
        ->getQuery()
        ->getSingleScalarResult();

    return $this->calculateEvolution($thisMonth, $lastMonth);
}
public function getRevenueEvolution(): float
{
    $startThisMonth = new \DateTime('first day of this month');
    $startLastMonth = new \DateTime('first day of last month');
    $endLastMonth = new \DateTime('last day of last month');

    $thisMonth = $this->historiqueRepository->createQueryBuilder('h')
        ->select('SUM(h.montant)')
        ->where('h.createdAt >= :start')
        ->setParameter('start', $startThisMonth)
        ->getQuery()
        ->getSingleScalarResult();

    $lastMonth = $this->historiqueRepository->createQueryBuilder('h')
        ->select('SUM(h.montant)')
        ->where('h.createdAt BETWEEN :start AND :end')
        ->setParameter('start', $startLastMonth)
        ->setParameter('end', $endLastMonth)
        ->getQuery()
        ->getSingleScalarResult();

    return $this->calculateEvolution($thisMonth ?? 0, $lastMonth ?? 0);
}
public function getTournamentsEvolution(): float
{
    $startThisMonth = new \DateTime('first day of this month');
    $startLastMonth = new \DateTime('first day of last month');
    $endLastMonth = new \DateTime('last day of last month');

    $thisMonth = $this->tournamentRepository->createQueryBuilder('t')
        ->select('COUNT(t)')
        ->where('t.createdAt >= :start')
        ->setParameter('start', $startThisMonth)
        ->getQuery()
        ->getSingleScalarResult();

    $lastMonth = $this->tournamentRepository->createQueryBuilder('t')
        ->select('COUNT(t)')
        ->where('t.createdAt BETWEEN :start AND :end')
        ->setParameter('start', $startLastMonth)
        ->setParameter('end', $endLastMonth)
        ->getQuery()
        ->getSingleScalarResult();

    return $this->calculateEvolution($thisMonth, $lastMonth);
}
public function getStreamsEvolution(): float
{
    $startThisMonth = new \DateTime('first day of this month');
    $startLastMonth = new \DateTime('first day of last month');
    $endLastMonth = new \DateTime('last day of last month');

    $thisMonth = $this->streamRepository->createQueryBuilder('s')
        ->select('COUNT(s)')
        ->where('s.createdAt >= :start')
        ->setParameter('start', $startThisMonth)
        ->getQuery()
        ->getSingleScalarResult();

    $lastMonth = $this->streamRepository->createQueryBuilder('s')
        ->select('COUNT(s)')
        ->where('s.createdAt BETWEEN :start AND :end')
        ->setParameter('start', $startLastMonth)
        ->setParameter('end', $endLastMonth)
        ->getQuery()
        ->getSingleScalarResult();

    return $this->calculateEvolution($thisMonth, $lastMonth);
}
}
