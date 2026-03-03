<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\ProfileEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\AvatarAiService;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Filesystem\Filesystem;

class RegisterController extends AbstractController
{
    #[Route('/register/avatar-preview', name: 'app_register_avatar_preview', methods: ['POST'])]
    public function avatarPreview(
        Request $request,
        AvatarAiService $avatarAi,
        SluggerInterface $slugger
    ): JsonResponse {
        $imageFile = $request->files->get('profileImage');
        if (!$imageFile) {
            return $this->json(['success' => false, 'error' => 'Aucune image reçue.']);
        }

        $tmpDir = $this->getParameter('profiles_directory') . '/tmp';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $safeFilename = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
        $tmpFilename  = $safeFilename . '-' . uniqid() . '.jpg';

        try {
            $imageFile->move($tmpDir, $tmpFilename);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Erreur de téléchargement.']);
        }

        $tmpPath = $tmpDir . '/' . $tmpFilename;
        $avatarBinary = $avatarAi->generateAvatar($tmpPath);

        if ($avatarBinary !== null) {
            file_put_contents($tmpPath, $avatarBinary);
            return $this->json([
                'success'    => true,
                'tempFile'   => $tmpFilename,
                'previewUrl' => '/uploads/profiles/tmp/' . $tmpFilename,
            ]);
        }

        // supprime le fichier temp en cas d'échec
        @unlink($tmpPath);
        return $this->json(['success' => false, 'error' => 'La génération IA a échoué.']);
    }

    #[Route('/register', name: 'app_register')]
public function register(
    Request $request,
    EntityManagerInterface $em,
    UserPasswordHasherInterface $passwordHasher,
    ValidatorInterface $validator,
    SluggerInterface $slugger,
    AvatarAiService $avatarAi
): Response {
    if ($request->isMethod('POST')) {

        $user = new User();

        $user->setName(trim($request->request->get('name')));
        $user->setEmail(trim($request->request->get('email')));

        // mot de passe EN CLAIR pour validation
        $plainPassword = $request->request->get('password');
        $user->setPassword($plainPassword);

        // 🔍 VALIDATION BACKEND
        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }

            return $this->render('security/register.html.twig');
        }

        // 🔒 email unique
        if ($em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()])) {
            $this->addFlash('error', 'Cet email est déjà utilisé.');
            return $this->render('security/register.html.twig');
        }

        // 🔐 hash APRÈS validation
        $user->setPassword(
            $passwordHasher->hashPassword($user, $plainPassword)
        );
        
        $user->setRoles(['ROLE_USER']);

        // Gestion de l'upload + conversion avatar IA (AvatarAiService)
        $profilesDir = $this->getParameter('profiles_directory');
        $tmpFile     = trim((string) $request->request->get('avatarTempFile'));
        $imageFile   = $request->files->get('profileImage');

        if ($tmpFile !== '' && preg_match('/^[\w\-]+\.jpg$/', $tmpFile)) {
            // Avatar déjà généré côté aperçu : on déplace le fichier temp
            $tmpPath  = $profilesDir . '/tmp/' . $tmpFile;
            $newFilename = $tmpFile;
            $destPath = $profilesDir . '/' . $newFilename;
            if (file_exists($tmpPath)) {
                rename($tmpPath, $destPath);
                $user->setProfileImage($newFilename);
            }
        } elseif ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename  = $safeFilename . '-' . uniqid() . '.jpg';
            $destPath     = $profilesDir . '/' . $newFilename;

            try {
                $imageFile->move($profilesDir, $newFilename);

                // Conversion en avatar anime via HuggingFace
                $avatarBinary = $avatarAi->generateAvatar($destPath);
                if ($avatarBinary !== null) {
                    file_put_contents($destPath, $avatarBinary);
                } else {
                    $this->addFlash('warning', 'La conversion en avatar IA a échoué (service indisponible). Votre photo originale a été conservée.');
                }

                $user->setProfileImage($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
            }
        }
    
        $em->persist($user);
        $em->flush();
        $response = $this->redirectToRoute('app_login');
        $emailFromRequest = $user->getEmail(); // ex: "user2%40gmail.com"
        $email = urldecode($emailFromRequest); 
        $cookie = Cookie::create('user_email')
            ->withValue($email)
            ->withExpires(strtotime('+1 day'));

        $response->headers->setCookie($cookie);

        $this->addFlash('success', 'Compte créé avec succès.');
        return $response;
    }

    return $this->render('security/register.html.twig');
}

    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(): Response
    {
        return $this->render('security/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    #[IsGranted('ROLE_USER')]
    public function editProfile(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier l'unicité de l'email si modifié
            $newEmail = $form->get('email')->getData();
            if ($newEmail !== $user->getEmail()) {
                $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $newEmail]);
                if ($existingUser && $existingUser->getId() !== $user->getId()) {
                    $this->addFlash('error', 'Cet email est déjà utilisé par un autre compte.');
                    return $this->render('security/edit_profile.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }

            // Gestion de l'upload de l'image de profil
            $imageFile = $form->get('profileImage')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('profiles_directory'), $newFilename);
                    
                    // Supprimer l'ancienne image si elle existe
                    if ($user->getProfileImage()) {
                        $oldImagePath = $this->getParameter('profiles_directory') . '/' . $user->getProfileImage();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    $user->setProfileImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            $em->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('security/edit_profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/profile/change-password', name: 'app_profile_change_password')]
    #[IsGranted('ROLE_USER')]
    public function changePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier le mot de passe actuel
            $currentPassword = $form->get('currentPassword')->getData();
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                return $this->render('security/profile_change_password.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Mettre à jour le mot de passe
            $newPassword = $form->get('newPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès !');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('security/profile_change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
