<?php

namespace App\Tests\Entity;

use App\Entity\Post;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testIsTrue()
    {
        $post = new Post();
        $post->setContent("Test content");

        $this->assertEquals("Test content", $post->getContent());
    }

    public function testIsEmpty()
    {
        $post = new Post();

        $this->assertEmpty($post->getContent());
    }
}