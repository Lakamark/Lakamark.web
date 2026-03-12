<?php

namespace App\Http\Controller;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Service\ResendConfirmationEmailService;
use Random\RandomException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class ResendConfirmationController extends AbstractController
{
    /**
     * @throws RandomException
     */
    #[Route(path: '/account/confirmation/resend', name: 'app_auth_resend_confirmation', methods: ['POST'])]
    public function sendConfirmationEmail(
        Security $security,
        ResendConfirmationEmailService $service,
    ): RedirectResponse {
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($user->isEmailConfirmed()) {
            $this->addFlash('info', 'Your email is already confirmed.');

            return $this->redirectToRoute('app_account');
        }

        $service->resend($user->getEmail());

        $this->addFlash(
            'success',
            'A new confirmation email has been sent.');

        return $this->redirectToRoute('app_account');
    }
}
