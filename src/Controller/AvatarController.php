<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AvatarController extends AbstractController
{
    #[Route('/api/avatar/cartoonify', name: 'api_avatar_cartoonify', methods: ['POST'])]
    public function cartoonify(Request $request): JsonResponse
    {
        $token = $_ENV['HUGGINGFACE_API_TOKEN'] ?? null;
        if (empty($token)) {
            return new JsonResponse(['error' => 'Token HuggingFace manquant dans .env'], 500);
        }

        // 1. Si prompt texte fourni, générer via SDXL (text-to-image)
        $prompt = $request->request->get('prompt');
        if ($prompt) {
            // Prompt fixe pour SDXL (valide en curl)
            $promptStatic = 'anime avatar portrait, professional, cartoon style, vibrant colors, detailed face, studio ghibli inspired, high quality';
            $result = $this->callSdxl($promptStatic, $token);
            if ($result && !str_starts_with($result, '{') && !str_starts_with($result, '[')) {
                // Correction : encode en JPEG si SDXL retourne du JPEG
                return new JsonResponse(['success' => true, 'image' => 'data:image/jpeg;base64,' . base64_encode($result)]);
            }
            return new JsonResponse(['error' => 'Erreur HuggingFace SDXL : ' . $result], 503);
        }

        // 2. Sinon, compatibilité image uploadée (ancien comportement)
        $file = $request->files->get('image');
        if (!$file) {
            return new JsonResponse(['error' => 'Aucune image recue'], 400);
        }
        $mime = $file->getMimeType();
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
            return new JsonResponse(['error' => 'Format non supporte'], 400);
        }
        $imageData = file_get_contents($file->getPathname());
        // (Optionnel) Appel image-to-image (deprecated sur HF gratuit)
        return new JsonResponse(['error' => 'Seul le mode prompt texte est supporté actuellement.'], 400);
    }

    /**
     * Appel SDXL (text-to-image)
     */
    private function callSdxl(string $prompt, string $token): ?string
    {
        $url = 'https://router.huggingface.co/hf-inference/models/stabilityai/stable-diffusion-xl-base-1.0';
        $data = json_encode(['inputs' => $prompt]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Accept: image/jpeg,image/png,*/*',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $errorMsg = null;
        if ($httpCode !== 200) {
            if (str_starts_with($contentType, 'application/json')) {
                $json = json_decode($response, true);
                $errorMsg = $json['error'] ?? $json['message'] ?? $response;
            } else {
                $errorMsg = $response;
            }
        }
        curl_close($ch);
        if ($httpCode === 200 && str_starts_with($contentType, 'image/')) {
            return $response;
        }
        return $errorMsg ?: null;
    }

    /**
     * Appel via router.huggingface.co — AnimeGAN2
     */
    /** @phpstan-ignore-next-line */
    private function callAnimeGan(string $imageData, string $token): ?string
    {
        // Ce modele accepte une image et retourne une image cartoon/anime
        $url = 'https://router.huggingface.co/hf-inference/models/bryandlee/animegan2-pytorch/v1/image-to-image';

        $boundary = '----FormBoundary' . uniqid();
        $body  = "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"inputs\"; filename=\"image.jpg\"\r\n";
        $body .= "Content-Type: image/jpeg\r\n\r\n";
        $body .= $imageData . "\r\n";
        $body .= "--{$boundary}--\r\n";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: multipart/form-data; boundary=' . $boundary,
                'Accept: image/png,image/jpeg,*/*',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($httpCode === 200 && str_starts_with($contentType, 'image/')) {
            return $response;
        }

        // Warm-up : reessayer
        if ($httpCode === 503) {
            sleep(5);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 90,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: multipart/form-data; boundary=' . $boundary,
                    'Accept: image/png,image/jpeg,*/*',
                ],
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($httpCode === 200 && str_starts_with($contentType, 'image/')) {
                return $response;
            }
        }

        return null;
    }

    /**
     * Fallback — LineMangaAI via router.huggingface.co
     */
    /** @phpstan-ignore-next-line */
    private function callImg2Img(string $imageData, string $token): ?string
    {
        $url = 'https://router.huggingface.co/hf-inference/models/ogkalu/comic-diffusion/v1/image-to-image';

        $boundary = '----FormBoundary' . uniqid();
        $body  = "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"inputs\"; filename=\"image.jpg\"\r\n";
        $body .= "Content-Type: image/jpeg\r\n\r\n";
        $body .= $imageData . "\r\n";
        $body .= "--{$boundary}--\r\n";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: multipart/form-data; boundary=' . $boundary,
                'Accept: image/png,image/jpeg,*/*',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($httpCode === 200 && str_starts_with($contentType, 'image/')) {
            return $response;
        }

        return null;
    }
}