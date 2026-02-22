<?php

namespace App\Service;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use Ably\AblyRest;

class AblyService
{
    private AblyRest $ably;

    public function __construct(#[Autowire('%env(ABLY_API_KEY)%')] string $apiKey)
    {
        $this->ably = new AblyRest($apiKey);
    }

    public function publish(string $channel, array $data): void
    {
        $this->ably->channel($channel)->publish('message', $data);
    }
}