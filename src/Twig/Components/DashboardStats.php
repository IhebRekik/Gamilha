<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent('dashboard_stats')]
class DashboardStats
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $refreshEverySeconds = 15;

    public function getUsersCount(): int
    {
        return 5600; // à remplacer par $this->userRepository->count([])
    }

    public function getActiveTournaments(): int
    {
        return 168;
    }

    public function getLiveStreamsCount(): int
    {
        return 34;
    }

    public function getMonthlyRevenue(): int
    {
        return 56000;
    }

    public function getRecentActivity(): array
    {
        return [
            '<i class="bi bi-trophy-fill text-warning me-2"></i> Nouveau tournoi <strong>"Valorant Masters"</strong> créé <small class="opacity-75">il y a 3 min</small>',
            '<i class="bi bi-person-plus-fill text-success me-2"></i> <strong>12</strong> nouveaux utilisateurs inscrits <small class="opacity-75">il y a 15 min</small>',
            '<i class="bi bi-broadcast text-danger me-2"></i> Stream <strong>"Pro League Finals"</strong> a atteint <strong>10K viewers</strong> <small class="opacity-75">il y a 1h</small>',
            '<i class="bi bi-currency-euro text-info me-2"></i> <strong>45</strong> nouveaux abonnements Pro <small class="opacity-75">il y a 2h</small>',
            '<i class="bi bi-people-fill text-primary me-2"></i> Équipe <strong>"Dragon Slayers"</strong> a rejoint la plateforme <small class="opacity-75">il y a 1j</small>',
        ];
    }
}