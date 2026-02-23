<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenRouterService
{
    private string $apiKey;
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client, string $openRouterApiKey)
    {
        $this->client = $client;
        $this->apiKey = $openRouterApiKey;
    }

    /**
     * Génère un texte à partir d'un prompt utilisateur via OpenRouter.
     */
    public function generateText(string $userInput): string
    {
        $systemPrompt = <<<PROMPT
Tu es un rédacteur spécialisé dans le gaming et l'e-sports.

Ta mission :
- Continuer le texte de l'utilisateur comme si c'était un post social.
- Ne jamais répondre comme un assistant.
- Ne jamais poser de question.
- Ne jamais faire d'introduction.
- Continue directement le texte fourni.
- Reste toujours dans le thème gaming / e-sports.
- Style naturel, comme un vrai post de communauté.
- Ajoute quelques emojis pertinents pour le gaming.

PROMPT;

        // Si l'utilisateur n'a rien écrit → sujet fun par défaut
        $finalInput = trim($userInput);
        if ($finalInput === '') {
            $finalInput = "Génère un post gaming fun et engageant sur un thème au choix (Valorant clutch, LoL ranked, Warzone killstreak, rage quit marrant, setup PC de ouf, etc.).";
        }

        try {
            $response = $this->client->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                    'HTTP-Referer'  => 'http://127.0.0.1:8000',
                    'X-Title'       => 'Esports Social App',
                ],
                'json' => [
                    'model' => 'z-ai/glm-4.5-air:free', // Modèle gratuit sûr
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $finalInput]
                    ],
                    'temperature'      => 1.0,
                    'max_tokens'       => 250,
                    'presence_penalty' => 0.2,
                ]
            ]);

            $content = $response->getContent();
            $json = json_decode($content, true);

            if (isset($json['choices'][0]['message']['content'])) {
                $suggestion = trim($json['choices'][0]['message']['content']);

          if ($suggestion === '') {
    // texte par défaut ou prompt fun
          $suggestion = "🎮 Génère un petit post gaming fun sur un thème aléatoire ! 😎🔥";
}

                return $suggestion;
            }

            // Erreur OpenRouter si pas de réponse valide
            throw new \Exception('Réponse invalide d’OpenRouter.');
        } catch (\Exception $e) {
            return "L'IA n'a pas pu générer de texte : " . $e->getMessage();
        }
    }
}