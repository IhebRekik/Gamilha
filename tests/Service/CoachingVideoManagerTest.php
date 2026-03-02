<?php
// 📁 tests/Service/CoachingVideoManagerTest.php

namespace App\Tests\Service;

use App\Entity\CoachingVideo;
use App\Service\CoachingVideoManager;
use PHPUnit\Framework\TestCase;

class CoachingVideoManagerTest extends TestCase
{
    private CoachingVideoManager $manager;

    protected function setUp(): void
    {
        $this->manager = new CoachingVideoManager();
    }

    // ✅ Cas nominal — tout est valide
    public function testVideoValide(): void
    {
        $video = new CoachingVideo();
        $video->setTitre('Cours de yoga débutant');
        $video->setNiveau('debutant');
        $video->setDuration(600);
        $video->setUrl('https://example.com/video.mp4');

        $this->assertTrue($this->manager->validate($video));
    }

    // ❌ Titre vide
    public function testTitreVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre est obligatoire.');

        $video = new CoachingVideo();
        $video->setTitre('');
        $video->setNiveau('avance');

        $this->manager->validate($video);
    }

    // ❌ Titre trop court (< 3 caractères)
    public function testTitreTropCourt(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('entre 3 et 255 caractères');

        $video = new CoachingVideo();
        $video->setTitre('AB');
        $video->setNiveau('intermediaire');

        $this->manager->validate($video);
    }

    // ❌ Niveau invalide
    public function testNiveauInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le niveau doit être');

        $video = new CoachingVideo();
        $video->setTitre('Pilates avancé');
        $video->setNiveau('expert'); // ❌ non autorisé

        $this->manager->validate($video);
    }

    // ❌ Durée négative
    public function testDureeNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La durée doit être supérieure à 0.');

        $video = new CoachingVideo();
        $video->setTitre('Stretching matinal');
        $video->setNiveau('debutant');
        $video->setDuration(-30); // ❌

        $this->manager->validate($video);
    }

    // ❌ URL malformée
    public function testUrlInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'URL fournie n\'est pas valide.');

        $video = new CoachingVideo();
        $video->setTitre('Cardio intense');
        $video->setNiveau('avance');
        $video->setUrl('pas-une-url'); // ❌

        $this->manager->validate($video);
    }

    // ✅ Durée et URL nulles — cas autorisé (champs optionnels)
    public function testVideoSansDureeNiUrl(): void
    {
        $video = new CoachingVideo();
        $video->setTitre('Méditation guidée');
        $video->setNiveau('intermediaire');

        $this->assertTrue($this->manager->validate($video));
    }
}