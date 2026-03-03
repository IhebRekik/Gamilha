<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserManager;
use PHPUnit\Framework\TestCase;

class UserManagerTest extends TestCase
{
    public function testValidUser()
    {
        $user = new User();
        $user->setName('Jihen');
        $user->setEmail('jihn@gmail.com');
        $user->setPassword('12345678');

        $manager = new UserManager();

        $this->assertTrue($manager->validate($user));
    }

    public function testUserWithoutName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setEmail('test@gmail.com');
        $user->setPassword('12345678');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testInvalidEmail()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setName('Test');
        $user->setEmail('email_invalide');
        $user->setPassword('12345678');

        $manager = new UserManager();
        $manager->validate($user);
    }
}