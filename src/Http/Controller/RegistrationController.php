<?php

namespace App\Http\Controller;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Service\ConfirmAccountService;
use App\Domain\Auth\Service\RegisterUserService;
use App\Http\Form\RegistrationFormType;
use Random\RandomException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    /**
     * @throws RandomException
     */
    #[Route(path: '/register', name: 'auth_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        RegisterUserService $registerUserService,
    ): Response {
        // We disabled captcha for testing purposes.
        $env = $this->getParameter('kernel.environment');
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'with_captcha_puzzle' => 'test' !== $env,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $registerUserService->register(
                user: $user,
                plainPassword: $plainPassword,
                request: $request,
            );

            $this->addFlash(
                'success',
                'Your account has been created. Please confirm your email.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/register/confirm', name: 'app_register_confirm', methods: ['GET'])]
    public function confirm(
        Request $request,
        ConfirmAccountService $confirmAccountService,
    ): RedirectResponse {
        $token = (string) $request->query->get('token', '');

        if ('' === $token) {
            $this->addFlash(
                'error',
                'Missing confirmation token.'
            );

            return $this->redirectToRoute('app_login');
        }

        try {
            $confirmAccountService->confirm($token);

            $this->addFlash(
                'success',
                'Your account has been confirmed. You can now sign in.'
            );
        } catch (\Throwable) {
            $this->addFlash(
                'error',
                'This confirmation link is invalid or expired.'
            );
        }

        return $this->redirectToRoute('app_login');
    }
}
