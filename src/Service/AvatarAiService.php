<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AvatarAiService
{
    private const SUBMIT_URL = 'https://router.huggingface.co/wavespeed/api/v3/wavespeed-ai/qwen-image/edit-plus-lora';
    private const PROMPT     = 'Convert this portrait into anime style';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $huggingFaceToken
    ) {}

    public function generateAvatar(string $absoluteImagePath): ?string
    {
        if (!file_exists($absoluteImagePath)) {
            return null;
        }

        $imageData = file_get_contents($absoluteImagePath);
        if ($imageData === false) {
            return null;
        }

        $b64     = base64_encode($imageData);
        $dataUrl = 'data:image/jpeg;base64,' . $b64;

        try {
            $response = $this->httpClient->request('POST', self::SUBMIT_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->huggingFaceToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'images'           => [$dataUrl],
                    'prompt'           => self::PROMPT,
                    'loras'            => [],
                    'seed'             => -1,
                    'output_format'    => 'jpeg',
                    'enable_sync_mode' => true,
                ],
                'timeout' => 120,
            ]);

            $data = $response->toArray(false);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $outputs = $data['data']['outputs']
                ?? $data['outputs']
                ?? [];

            if (empty($outputs)) {
                return null;
            }

            $outUrl = $outputs[0];

            if (str_starts_with($outUrl, 'data:')) {
                $base64Data = explode(',', $outUrl, 2)[1] ?? '';
                return base64_decode($base64Data) ?: null;
            }

            $imgResponse = $this->httpClient->request('GET', $outUrl, ['timeout' => 60]);
            return $imgResponse->getContent(false) ?: null;

        } catch (\Throwable) {
            return null;
        }
    }
}