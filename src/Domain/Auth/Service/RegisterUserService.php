<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\DTO\IssuedTokenRequestDTO;
use App\Domain\Auth\DTO\RegisterUserResultDTO;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\OAuthProvider;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Event\BeforeUserRegisterEvent;
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

        $issuedTokenRequest = null;

        // Issue a confirmation token only for local registrations.
        if (OAuthProvider::LOCAL === $authProvider) {
            $issuedTokenRequest = $this->tokenRequestService->issue(
                user: $user,
                type: TokenRequestType::REGISTER_CONFIRMATION,
            );
        }

        $this->dispatchUserRegistered($user, $authProvider, $issuedTokenRequest);

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
        ?IssuedTokenRequestDTO $issuedTokenRequest,
    ): void {
        $this->dispatcher->dispatch(
            new UserRegisteredEvent($user, $authProvider, $issuedTokenRequest)
        );
    }
}
