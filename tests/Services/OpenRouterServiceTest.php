<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\OpenRouterService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class OpenRouterServiceTest extends TestCase
{
    public function testGenerateTextReturnsSuggestion()
    {
        $fakeResponse = $this->createMock(ResponseInterface::class);
        $fakeResponse->method('getContent')
            ->willReturn(json_encode([
                'choices' => [
                    [
                        'message' => [
                            'content' => '🔥 Ace clutch en ranked incroyable !'
                        ]
                    ]
                ]
            ]));

        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockClient->method('request')
            ->willReturn($fakeResponse);

        $service = new OpenRouterService($mockClient, 'fake_key');

        $result = $service->generateText('Valorant clutch');

        $this->assertEquals(
            '🔥 Ace clutch en ranked incroyable !',
            $result
        );
    }
}