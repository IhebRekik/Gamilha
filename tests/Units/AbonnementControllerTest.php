<?php

namespace App\Tests\Unit;

use App\Controller\AbonnementController;
use App\Entity\Abonnement;
use App\Repository\AbonnementRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AbonnementControllerTest extends TestCase
{

    public function testDeleteRemovesAbonnementWhenCsrfIsValid(): void
    {
        $abonnement = new Abonnement();

        // On fake getId()
        $reflection = new \ReflectionClass($abonnement);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($abonnement, 5);

        $repoMock = $this->createMock(AbonnementRepository::class);
        $repoMock->method('find')->willReturn($abonnement);

        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock->expects($this->once())->method('remove');
        $emMock->expects($this->once())->method('flush');

        $request = new Request([], ['_token' => 'valid_token']);

        $controller = $this->getMockBuilder(AbonnementController::class)
            ->onlyMethods(['isCsrfTokenValid', 'redirectToRoute'])
            ->getMock();

        $controller->method('isCsrfTokenValid')->willReturn(true);
        $controller->method('redirectToRoute')
            ->willReturn(new RedirectResponse('/'));

        $response = $controller->delete(5, $request, $repoMock, $emMock);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
    public function testEditFlushAndRedirectWhenFormIsValid(): void
{
    $abonnement = new \App\Entity\Abonnement();

    // Mock repository
    $repoMock = $this->createMock(\App\Repository\AbonnementRepository::class);
    $repoMock->method('find')
             ->willReturn($abonnement);

    // Mock EntityManager
    $emMock = $this->createMock(EntityManagerInterface::class);
    $emMock->expects($this->once())
           ->method('flush');

    // Fake request
    $request = new Request();

    // Mock Form
    $formMock = $this->createMock(FormInterface::class);
    $formMock->method('handleRequest');
    $formMock->method('isSubmitted')->willReturn(true);
    $formMock->method('isValid')->willReturn(true);

    // Mock Controller partiellement
    $controller = $this->getMockBuilder(\App\Controller\AbonnementController::class)
        ->onlyMethods(['createForm', 'redirectToRoute'])
        ->getMock();

    $controller->method('createForm')
               ->willReturn($formMock);

    $controller->method('redirectToRoute')
               ->willReturn(new RedirectResponse('/'));

    $response = $controller->edit(1, $request, $repoMock, $emMock);

    $this->assertInstanceOf(RedirectResponse::class, $response);
}
}
