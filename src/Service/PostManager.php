<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;

class PostManager
{
    public function validate(Post $post): bool
    {
        if (empty($post->getContent())) {
            throw new \InvalidArgumentException('Le contenu est obligatoire');
        }

        if (strlen($post->getContent()) < 12) {
            throw new \InvalidArgumentException('Le contenu doit contenir au moins 12 caractères');
        }

        if (!$post->getUser()) {
            throw new \InvalidArgumentException('Le post doit avoir un auteur');
        }

        return true;
    }

    public function like(Post $post, User $user): bool
    {
        if ($post->getUser() === $user) {
            throw new \InvalidArgumentException('Vous ne pouvez pas liker votre propre post');
        }

        if ($post->isLikedBy($user)) {
            throw new \InvalidArgumentException('Post déjà liké');
        }

        $post->addLikedBy($user);

        return true;
    }
}