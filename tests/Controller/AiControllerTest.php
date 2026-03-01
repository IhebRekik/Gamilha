<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AiControllerTest extends WebTestCase
{
    public function testEmptyPromptReturns400()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/ai/suggest',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['prompt' => ''])
        );

        $this->assertResponseStatusCodeSame(400);
    }
}