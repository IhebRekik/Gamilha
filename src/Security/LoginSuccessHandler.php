<?php
// src/Security/LoginSuccessHandler.php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();

        /** @var User $user */
        $cookie = Cookie::create('user_email')
            ->withValue($user->getEmail())
            ->withExpires(strtotime('+1 day'))
            ->withSecure(false)   // mettre true si HTTPS
            ->withHttpOnly(true);
        if (in_array('ROLE_ADMIN', $user->getRoles())) {

            return new RedirectResponse(
                $this->router->generate('admin_dashboard'),302,
            ['Set-Cookie' => $cookie]
            );
        }

        return new RedirectResponse(
            $this->router->generate('app_homepage'),
            302,
            ['Set-Cookie' => $cookie]
        );
    }
}
