<?php
// 📁 src/Service/CoachingVideoManager.php

namespace App\Service;

use App\Entity\CoachingVideo;

class CoachingVideoManager
{
    private const NIVEAUX_VALIDES = ['debutant', 'intermediaire', 'avance'];

    public function validate(CoachingVideo $video): bool
    {
        // Règle 1 — Titre obligatoire et longueur entre 3 et 255
        $titre = $video->getTitre();
        if (empty($titre)) {
            throw new \InvalidArgumentException('Le titre est obligatoire.');
        }
        if (strlen($titre) < 3 || strlen($titre) > 255) {
            throw new \InvalidArgumentException('Le titre doit faire entre 3 et 255 caractères.');
        }

        // Règle 2 — Niveau valide
        if (!in_array($video->getNiveau(), self::NIVEAUX_VALIDES, true)) {
            throw new \InvalidArgumentException('Le niveau doit être : debutant, intermediaire ou avance.');
        }

        // Règle 3 — Durée positive si renseignée
        if ($video->getDuration() !== null && $video->getDuration() <= 0) {
            throw new \InvalidArgumentException('La durée doit être supérieure à 0.');
        }

        // Règle 4 — URL valide si renseignée
        if ($video->getUrl() !== null && !filter_var($video->getUrl(), FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('L\'URL fournie n\'est pas valide.');
        }

        return true;
    }
}