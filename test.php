<?php
// update_password.php

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

// charger l'environnement
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$kernel = new \App\Kernel('dev', true);
$kernel->boot();

$entityManager = $kernel->getContainer()->get(EntityManagerInterface::class);
$passwordHasher = $kernel->getContainer()->get(UserPasswordHasherInterface::class);

$userRepository = $entityManager->getRepository(User::class);

// ----------------------------
// Mettre à jour le mot de passe
// ----------------------------
$plainPassword = '12345678'; // ton mot de passe en clair
$user = $userRepository->findOneBy(['email' => 'test@gmail.com']);

if (!$user) {
    die("Utilisateur introuvable.\n");
}

$hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
$user->setPassword($hashedPassword);

$entityManager->flush();

echo "Mot de passe mis à jour avec succès !\n";
