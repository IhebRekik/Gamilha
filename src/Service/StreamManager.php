<?php

namespace App\Service;

use App\Entity\Stream;

class StreamManager
{
    public function validate(Stream $stream): bool
    {
        if (empty($stream->getTitle())) {
            throw new \InvalidArgumentException('Le titre est obligatoire');
        }

        if (strlen($stream->getTitle()) < 3) {
            throw new \InvalidArgumentException('Le titre doit contenir au moins 3 caractères');
        }

        if ($stream->getUser() === null) {
            throw new \InvalidArgumentException('Un stream doit être associé à un utilisateur');
        }

        return true;
    }
}