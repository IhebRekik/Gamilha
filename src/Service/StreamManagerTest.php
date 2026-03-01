<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\StreamManager;
use App\Entity\Stream;
use App\Entity\User;

class StreamManagerTest extends TestCase
{
    private StreamManager $streamManager;

    protected function setUp(): void
    {
        $this->streamManager = new StreamManager();
    }

    // ✅ Test valide
    public function testValidateSuccess(): void
    {
        $stream = new Stream();
        $stream->setTitle('Mon super stream');
        $stream->setUser(new User());

        $this->assertTrue($this->streamManager->validate($stream));
    }

    //  Titre vide
    public function testValidateFailsIfTitleIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre est obligatoire');

        $stream = new Stream();
        $stream->setTitle('');
        $stream->setUser(new User());

        $this->streamManager->validate($stream);
    }

    //  Titre trop court
    public function testValidateFailsIfTitleTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre doit contenir au moins 3 caractères');

        $stream = new Stream();
        $stream->setTitle('ab');
        $stream->setUser(new User());

        $this->streamManager->validate($stream);
    }

    //  Pas d'utilisateur
    public function testValidateFailsIfNoUser(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Un stream doit être associé à un utilisateur');

        $stream = new Stream();
        $stream->setTitle('Stream valide');

        $this->streamManager->validate($stream);
    }
}