<?php

namespace App\Tests\Unit;

use App\Controller\ChatMessageController;
use App\Entity\ChatMessage;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class ChatMessageControllerTest extends TestCase
{
    public function testEditAjaxUpdatesMessageContent(): void
    {
        // Fake message
        $message = new ChatMessage();

        // Fake request JSON
        $request = new Request([], [], [], [], [], [], json_encode([
            'content' => 'New content'
        ]));

        // Mock EntityManager
        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock->expects($this->once())
            ->method('flush');

        $controller = new ChatMessageController();

        $response = $controller->editAjax($message, $request, $emMock);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('New content', $message->getContent());
        $this->assertEquals('{"status":"success"}', $response->getContent());
    }
    public function testNewMessagePersistsWhenFormIsValid()
{
    $recipientId = 2;

    $request = new Request();
    $request->cookies->set('user_email', 'test@email.com');

    // Fake sender user
    $sender = $this->createMock(\App\Entity\User::class);

    // Fake recipient user
    $recipient = $this->createMock(\App\Entity\User::class);

    $userRepository = $this->createMock(\App\Repository\UserRepository::class);

    // IMPORTANT : mock findOneBy (pour sender via email)
    $userRepository->method('findOneBy')
        ->willReturn($sender);

    // Mock find (pour recipient via ID)
    $userRepository->method('find')
        ->willReturn($recipient);

    $emMock = $this->createMock(EntityManagerInterface::class);
    $emMock->expects($this->once())->method('persist');
    $emMock->expects($this->once())->method('flush');

    $controller = $this->getMockBuilder(ChatMessageController::class)
        ->onlyMethods(['createForm', 'redirectToRoute'])
        ->getMock();

    $formMock = $this->createMock(\Symfony\Component\Form\FormInterface::class);
    $formMock->method('handleRequest');
    $formMock->method('isSubmitted')->willReturn(true);
    $formMock->method('isValid')->willReturn(true);

    $controller->method('createForm')->willReturn($formMock);
    $controller->method('redirectToRoute')
        ->willReturn(new RedirectResponse('/'));

    $response = $controller->new(
        $recipientId,
        $request,
        $emMock,
        $userRepository
    );

    $this->assertInstanceOf(Response::class, $response);
}
}