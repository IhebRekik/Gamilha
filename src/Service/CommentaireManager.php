<?php

namespace App\Service;

use App\Entity\Commentaire;

class CommentaireManager
{
    public function validate(Commentaire $comment): bool
    {
        if (empty($comment->getText())) {
            throw new \InvalidArgumentException('Commentaire vide');
        }

        if (strlen($comment->getText()) < 5) {
            throw new \InvalidArgumentException('Minimum 5 caractères');
        }

        if (strlen($comment->getText()) > 500) {
            throw new \InvalidArgumentException('Maximum 500 caractères');
        }

        if (!$comment->getUser()) {
            throw new \InvalidArgumentException('Utilisateur obligatoire');
        }

        if (!$comment->getPost()) {
            throw new \InvalidArgumentException('Post obligatoire');
        }

        return true;
    }
}