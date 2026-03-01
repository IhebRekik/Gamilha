<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Commentaire;

class CommentaireTest extends TestCase
{
    public function testSetText()
    {
        $comment = new Commentaire();
        $comment->setText("Super post gaming !");

        $this->assertEquals("Super post gaming !", $comment->getText());
    }
}