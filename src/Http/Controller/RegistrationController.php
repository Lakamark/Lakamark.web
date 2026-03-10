<?php

namespace App\Http\Controller;

use App\Domain\Auth\Authenticator;
use App\Domain\Auth\Service\TokenRequestService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $em,
        TokenRequestService $tokenRequestService,
        EventDispatcherInterface $dispatcher,
        UserAuthenticatorInterface $userAuthenticator,
        Authenticator $authenticator,
    ): Response {
        return $this->render('registration/register.html.twig');
    }

    #[Route('/register/confirmation', name: 'app_register_confirm', methods: ['GET'])]
    public function confirm(
        Request $request,
        TokenRequestService $tokenRequestService,
        EntityManagerInterface $em,
    ): RedirectResponse {
        return $this->redirectToRoute('app_login');
    }
}
