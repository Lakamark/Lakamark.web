<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\DTO\IssuedTokenRequestDTO;
use App\Domain\Auth\DTO\RegisterUserResultDTO;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\ConfirmationEmailReason;
use App\Domain\Auth\Enum\OAuthProvider;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Event\BeforeUserRegisterEvent;
use App\Domain\Auth\Event\ConfirmationEmailRequestedEvent;
use App\Domain\Auth\Event\ConfirmationTokenIssuedEvent;
use App\Domain\Auth\Event\UserRegisteredEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Random\RandomException;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class RegisterUserService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em,
        private TokenRequestService $tokenRequestService,
        private EventDispatcherInterface $dispatcher,
        private ClockInterface $clock,
    ) {
    }

    /**
     * Register a new user and optionally issue a confirmation token.
     *
     * @throws RandomException
     */
    public function register(
        User $user,
        string $plainPassword,
        Request $request,
        OAuthProvider $authProvider = OAuthProvider::LOCAL,
    ): RegisterUserResultDTO {
        $user->setCreatedAt($this->clock->now());

        if (OAuthProvider::LOCAL === $authProvider) {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $plainPassword)
            );
        }

        $this->dispatchBeforeRegister($user, $request, $authProvider);

        $this->em->persist($user);
        $this->em->flush();

        $this->dispatchUserRegistered($user, $authProvider);

        $issuedTokenRequest = null;

        if (OAuthProvider::LOCAL === $authProvider) {
            $issuedTokenRequest = $this->tokenRequestService->issue(
                user: $user,
                type: TokenRequestType::REGISTER_CONFIRMATION,
            );

            $this->dispatchConfirmationTokenIssued($user, $issuedTokenRequest);
            $this->dispatchConfirmationEmailRequested(
                $user,
                $issuedTokenRequest,
                ConfirmationEmailReason::REGISTER,
            );
        }

        return new RegisterUserResultDTO(
            user: $user,
            authProvider: $authProvider,
            issuedTokenRequest: $issuedTokenRequest,
        );
    }

    private function dispatchBeforeRegister(
        User $user,
        Request $request,
        OAuthProvider $authProvider,
    ): void {
        $this->dispatcher->dispatch(
            new BeforeUserRegisterEvent($user, $request, $authProvider)
        );
    }

    private function dispatchUserRegistered(
        User $user,
        OAuthProvider $authProvider,
    ): void {
        $this->dispatcher->dispatch(
            new UserRegisteredEvent($user, $authProvider)
        );
    }

    private function dispatchConfirmationTokenIssued(
        User $user,
        IssuedTokenRequestDTO $issuedTokenRequest,
    ): void {
        $this->dispatcher->dispatch(
            new ConfirmationTokenIssuedEvent($user, $issuedTokenRequest),
        );
    }

    private function dispatchConfirmationEmailRequested(
        User $user,
        IssuedTokenRequestDTO $issuedTokenRequest,
        ConfirmationEmailReason $reason,
    ): void {
        $this->dispatcher->dispatch(
            new ConfirmationEmailRequestedEvent($user, $issuedTokenRequest, $reason)
        );
    }
}
