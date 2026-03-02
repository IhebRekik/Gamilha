<?php

namespace App\Tests\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Service\PostManager;
use PHPUnit\Framework\TestCase;

class PostManagerTest extends TestCase
{
    public function testValidPost()
    {
        $post = new Post();
        $user = new User();

        $post->setContent('Ceci est un contenu valide.');
        $post->setUser($user);

        $manager = new PostManager();

        $this->assertTrue($manager->validate($post));
    }

    public function testPostWithoutContent()
    {
        $this->expectException(\InvalidArgumentException::class);

        $post = new Post();
        $post->setContent('');
        $post->setUser(new User());

        $manager = new PostManager();
        $manager->validate($post);
    }

    public function testPostWithShortContent()
    {
        $this->expectException(\InvalidArgumentException::class);

        $post = new Post();
        $post->setContent('Trop court');
        $post->setUser(new User());

        $manager = new PostManager();
        $manager->validate($post);
    }

    public function testPostWithoutUser()
    {
        $this->expectException(\InvalidArgumentException::class);

        $post = new Post();
        $post->setContent('Contenu valide mais sans user');

        $manager = new PostManager();
        $manager->validate($post);
    }

    public function testLikeOwnPost()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $post = new Post();
        $post->setContent('Contenu valide pour le test');
        $post->setUser($user);

        $manager = new PostManager();
        $manager->like($post, $user);
    }
}