<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiVideoService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private string $baseUrl = 'https://ws.api.video'; // ou sandbox.api.video pour tests

    public function __construct(
        HttpClientInterface $client,
        #[Autowire('%env(APIVIDEO_API_KEY)%')] string $apiKey
    ) {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function createLiveStream(string $name): array
    {
        $token = $this->getAccessToken();

        $response = $this->client->request('POST', $this->baseUrl . '/live-streams', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'name'   => $name,     // ← obligatoire : 'name' et non 'title'
                'public' => true,      // optionnel, mais recommandé
            ],
        ]);

        $data = $response->toArray();

        return [
            'apiVideoId'  => $data['liveStreamId'] ?? null,
            'name'        => $data['name'] ?? $name,
            'streamKey'   => $data['streamKey'] ?? null,
            'rtmpServer'  => 'rtmp://broadcast.api.video/s', // fixe pour tous
            'playerUrl'   => $data['assets']['player'] ?? null,   // https://embed.api.video/live/...
            'hlsUrl'      => $data['assets']['hls'] ?? null,
            'broadcasting'=> $data['broadcasting'] ?? false,
        ];
    }

    private function getAccessToken(): string
    {
        $response = $this->client->request('POST', $this->baseUrl . '/auth/api-key', [
            'json' => ['apiKey' => $this->apiKey],
        ]);

        $data = $response->toArray();
        return $data['access_token'] ?? throw new \RuntimeException('Token api.video non obtenu');
    }
}