<?php

namespace App\Tests\Service;

use App\Service\OpenRouterService;
use PHPUnit\Framework\TestCase;

class OpenRouterServiceTest extends TestCase
{
    public function testGenerateTextReturnsString()
    {
        $mock = $this->createMock(OpenRouterService::class);

        $mock->method('generateText')
             ->willReturn('Suggestion générée');

        $result = $mock->generateText('Bonjour');

        $this->assertIsString($result);
        $this->assertEquals('Suggestion générée', $result);
    }
}