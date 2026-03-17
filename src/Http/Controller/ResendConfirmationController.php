<?php

namespace App\Http\Controller;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Service\ResendConfirmationEmailService;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class ResendConfirmationController extends AbstractController
{
    /**
     * @throws RandomException
     */
    #[Route(
        path: '/account/confirmation/resend',
        name: 'app_auth_resend_confirmation',
        methods: ['POST']
    )]
    #[IsCsrfTokenValid('submit', tokenKey: '_csrf_token')]
    public function sendConfirmationEmail(
        Security $security,
        ResendConfirmationEmailService $service,
        RateLimiterFactoryInterface $resendConfirmationEmailLimiter,
    ): RedirectResponse {
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $rateLimit = $resendConfirmationEmailLimiter->create($user->getUserIdentifier())->consume(1);

        if (!$rateLimit->isAccepted()) {
            $this->addFlash(
                'error',
                'Too many confirmation email requests. Please try again later.'
            );

            return $this->redirectToRoute('app_account');
        }

        $result = $service->resend($user->getEmail());

        if ($result->alreadyConfirmed) {
            $this->addFlash('info', 'Your email is already confirmed.');

            return $this->redirectToRoute('app_account');
        }

        if (!$result->success) {
            $this->addFlash('error', 'Unable to resend the confirmation email.');

            return $this->redirectToRoute('app_account');
        }

        $this->addFlash('success', 'A new confirmation email has been sent.');

        return $this->redirectToRoute('app_account');
    }
}
