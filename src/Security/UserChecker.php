<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\DisabledException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Vérifier si le compte est désactivé
        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException('Votre compte a été désactivé. Veuillez contacter l\'administrateur.');
        }

        // Vérifier si le compte est banni
        $banUntil = $user->getBanUntil();
        if ($banUntil instanceof \DateTimeImmutable && $banUntil > new \DateTimeImmutable()) {
            $banMessage = sprintf(
                'Votre compte a été banni jusqu\'au %s. Raison : violation des règles de la communauté.',
                $banUntil->format('d/m/Y à H:i')
            );
            throw new CustomUserMessageAccountStatusException($banMessage);
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Vérifications post-authentification si nécessaire
    }
}
