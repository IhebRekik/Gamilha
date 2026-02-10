<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
public function register(
    Request $request,
    EntityManagerInterface $em,
    UserPasswordHasherInterface $passwordHasher,
    ValidatorInterface $validator
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
}
