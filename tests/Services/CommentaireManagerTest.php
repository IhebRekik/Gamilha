<?php

namespace App\Tests\Service;

use App\Entity\Commentaire;
use App\Entity\Post;
use App\Entity\User;
use App\Service\CommentaireManager;
use PHPUnit\Framework\TestCase;

class CommentaireManagerTest extends TestCase
{
    public function testValidComment()
    {
        $comment = new Commentaire();
        $comment->setText('Commentaire valide');
        $comment->setUser(new User());
        $comment->setPost(new Post());

        $this->assertTrue((new CommentaireManager())->validate($comment));
    }

    public function testEmptyComment()
    {
        $this->expectException(\InvalidArgumentException::class);

        $comment = new Commentaire();
        $comment->setText('');
        $comment->setUser(new User());
        $comment->setPost(new Post());

        (new CommentaireManager())->validate($comment);
    }

    public function testTooShortComment()
    {
        $this->expectException(\InvalidArgumentException::class);

        $comment = new Commentaire();
        $comment->setText('abc');
        $comment->setUser(new User());
        $comment->setPost(new Post());

        (new CommentaireManager())->validate($comment);
    }
}