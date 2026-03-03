<?php

namespace App\Service;

use App\Entity\User;

class UserManager
{
    public function validate(User $user): bool
    {
        if (empty($user->getName())) {
            throw new \InvalidArgumentException('Le nom est obligatoire');
        }

        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email invalide');
        }

        if (strlen($user->getPassword()) < 8) {
            throw new \InvalidArgumentException('Mot de passe trop court');
        }

        return true;
    }
}