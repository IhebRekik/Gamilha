<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class PasswordResetController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'Aucun compte n\'est associé à cette adresse email.');
                return $this->redirectToRoute('app_forgot_password');
            }

            // Générer un code à 6 chiffres
            $code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            // Créer le token de réinitialisation
            $resetToken = new PasswordResetToken();
            $resetToken->setEmail($email);
            $resetToken->setCode($code);
            $resetToken->setExpiresAt(new \DateTime('+15 minutes'));

            $em->persist($resetToken);
            $em->flush();

            // Envoyer l'email
            try {
                $emailMessage = (new Email())
                    ->from('noreply@gamilha.com')
                    ->to($email)
                    ->subject('Réinitialisation de votre mot de passe - Gamilha')
                    ->html($this->renderView('emails/password_reset.html.twig', [
                        'code' => $code,
                        'userName' => $user->getName()
                    ]));

                $mailer->send($emailMessage);

                $this->addFlash('success', 'Un code de vérification a été envoyé à votre adresse email.');
                return $this->redirectToRoute('app_reset_password_verify', ['email' => $email]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.');
                return $this->redirectToRoute('app_forgot_password');
            }
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/verify', name: 'app_reset_password_verify')]
    public function verifyCode(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $email = $request->query->get('email');

        if (!$email) {
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');

            $resetToken = $em->getRepository(PasswordResetToken::class)->findOneBy([
                'email' => $email,
                'code' => $code,
                'isUsed' => false
            ]);

            if (!$resetToken) {
                $this->addFlash('error', 'Code de vérification invalide.');
                return $this->redirectToRoute('app_reset_password_verify', ['email' => $email]);
            }

            if ($resetToken->isExpired()) {
                $this->addFlash('error', 'Ce code a expiré. Veuillez demander un nouveau code.');
                return $this->redirectToRoute('app_forgot_password');
            }

            // Code valide, rediriger vers la page de changement de mot de passe
            return $this->redirectToRoute('app_reset_password_change', [
                'email' => $email,
                'code' => $code
            ]);
        }

        return $this->render('security/verify_reset_code.html.twig', [
            'email' => $email
        ]);
    }

    #[Route('/reset-password/change', name: 'app_reset_password_change')]
    public function changePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $email = $request->query->get('email');
        $code = $request->query->get('code');

        if (!$email || !$code) {
            return $this->redirectToRoute('app_forgot_password');
        }

        // Vérifier que le code est toujours valide
        $resetToken = $em->getRepository(PasswordResetToken::class)->findOneBy([
            'email' => $email,
            'code' => $code,
            'isUsed' => false
        ]);

        if (!$resetToken || $resetToken->isExpired()) {
            $this->addFlash('error', 'Code invalide ou expiré.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_reset_password_change', [
                    'email' => $email,
                    'code' => $code
                ]);
            }

            if (strlen($newPassword) < 8) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
                return $this->redirectToRoute('app_reset_password_change', [
                    'email' => $email,
                    'code' => $code
                ]);
            }

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'Utilisateur non trouvé.');
                return $this->redirectToRoute('app_forgot_password');
            }

            // Mettre à jour le mot de passe
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            
            // Marquer le token comme utilisé
            $resetToken->setIsUsed(true);

            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/change_password.html.twig', [
            'email' => $email,
            'code' => $code
        ]);
    }
}
