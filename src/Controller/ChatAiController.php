<?php

namespace App\Controller;

use App\Entity\ChatAi;
use App\Entity\User;
use App\Form\ChatAiType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ChatAiController extends AbstractController
{
    #[Route('/chat/ai', name: 'chat')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        HttpClientInterface $client,
        UserRepository $userRepository
    ): Response {

        $message = new ChatAi();
        $form = $this->createForm(ChatAiType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User|null $user */
            $user = $this->getUser();

            if (!$user instanceof User) {
                throw new \LogicException('User not found');
            }
            $message->setRole('user');
            $message->setUser($user);

            // 🔥 Vich gère automatiquement imageFile et audioFile
            $em->persist($message);
            $em->flush();

            // -----------------------
            // 🧠 HISTORIQUE
            // -----------------------
            $history = $em->getRepository(ChatAi::class)
                ->findBy(['user' => $user], ['createdAt' => 'ASC']);

            $prompt = "
Tu es un coach professionnel pour des jeux de E-sports tu repondre juste dans ce domaine.

else reponds que tu ne peux pas aider en dehors de ce domaine.

Répond toujours de manière professionnelle.
\n\n";

            foreach ($history as $msg) {
                if ($msg->getRole() === 'user') {
                    $prompt .= "Joueur: " . $msg->getContent() . "\n";
                } else {
                    $prompt .= "Coach: " . $msg->getContent() . "\n";
                }
            }

            $prompt .= "Coach:";

            // -----------------------
            // 🤖 APPEL OLLAMA
            // -----------------------
            try {
                $response = null;
                if ($message->getImageName()) {
                    $imageName = $message->getImageName();
                    if (in_array(strtolower(pathinfo($imageName, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {

                        $imagePath = $this->getParameter('kernel.project_dir')
                            . '/public/uploads/images/' . $message->getImageName();

                        $imageBase64 = base64_encode(file_get_contents($imagePath));

                        $response = $client->request('POST', 'http://127.0.0.1:11434/api/generate', [
                            'json' => [
                                'model' => 'llama3:8b',
                                'prompt' => $prompt,
                                'images' => [$imageBase64],
                                'stream' => false,
                                'options' => [
                                    'num_predict' => 120,   // limite la longueur réponse
                                    'num_ctx' => 2048,      // réduit le contexte
                                    'temperature' => 0.7,
                                    'top_k' => 40,
                                    'top_p' => 0.9
                                ]
                            ]
                        ]);
                    } else {
                        $aiReply = "Erreur  : Extension d'image non supportée";
                    }
                } else {

                    $response = $client->request('POST', 'http://127.0.0.1:11434/api/generate', [
                        'json' => [
                            'model' => 'llama3:8b',
                            'prompt' => $prompt,
                            'stream' => false,
                            'options' => [
                                'num_predict' => 120,   // limite la longueur réponse
                                'num_ctx' => 2048,      // réduit le contexte
                                'temperature' => 0.7,
                                'top_k' => 40,
                                'top_p' => 0.9
                            ]
                        ]
                    ]);
                }

                $data = $response->toArray();
                $aiReply = $data['response'] ?? 'Erreur IA';
            } catch (\Exception $e) {

                $aiReply = "Erreur Ollama : " . $e->getMessage();
            }

            // -----------------------
            // 🤖 MESSAGE IA
            // -----------------------
            $assistant = new ChatAi();
            $assistant->setContent($aiReply);
            $assistant->setRole('assistant');
            /** @var User|null $user */
            $user = $this->getUser();

            if (!$user instanceof User) {
                throw new \LogicException('User not found');
            }
            $assistant->setUser($user);

            $em->persist($assistant);
            $em->flush();

            return $this->redirectToRoute('chat');
        }
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('User not found');
        }

        $messages = $em->getRepository(ChatAi::class)
            ->findBy(['user' => $user], ['createdAt' => 'ASC']);

        return $this->render('chat_ai/index.html.twig', [
            'form' => $form->createView(),
            'messages' => $messages
        ]);
    }
}
