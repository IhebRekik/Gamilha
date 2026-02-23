<?php
namespace App\Controller;

use App\Service\OpenRouterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AiController extends AbstractController
{
private OpenRouterService $openRouter;

    public function __construct(OpenRouterService $openRouter)
    {
        $this->openRouter = $openRouter;
    }

    #[Route('/ai/suggest', name: 'ai_suggest_text', methods: ['POST'])]
    public function suggest(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $prompt = trim($data['prompt'] ?? '');

        if (empty($prompt)) {
            return new JsonResponse(['success' => false, 'error' => 'Texte vide'], 400);
        }

        try {
            $suggestion = $this->openRouter->generateText($prompt);
            return new JsonResponse(['success' => true, 'suggestion' => $suggestion]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }}