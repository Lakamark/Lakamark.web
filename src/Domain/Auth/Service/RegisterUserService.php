<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\DTO\IssuedTokenRequestDTO;
use App\Domain\Auth\DTO\RegisterUserResultDTO;
use App\Domain\Auth\Entity\User;
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
        bool $isOauthRequest = false,
    ): RegisterUserResultDTO {
        $user
            ->setPassword($this->passwordHasher->hashPassword($user, $plainPassword))
            ->setCreatedAt($this->clock->now());

        $this->dispatchBeforeRegister($user, $request);

        $this->em->persist($user);
        $this->em->flush();

        $issuedTokenRequest = null;

        // Issue a confirmation token only for non-OAuth registrations.
        if (!$isOauthRequest) {
            $issuedTokenRequest = $this->tokenRequestService->issue(
                user: $user,
                type: TokenRequestType::REGISTER_CONFIRMATION,
            );

            $this->dispatchUserRegistered($issuedTokenRequest);
        }

        return new RegisterUserResultDTO(
            user: $user,
            isOauthRequest: $isOauthRequest,
            issuedTokenRequest: $issuedTokenRequest,
        );
    }

    private function dispatchBeforeRegister(
        User $user,
        Request $request,
    ): void {
        $this->dispatcher->dispatch(
            new BeforeUserRegisterEvent($user, $request)
        );
    }

    private function dispatchUserRegistered(
        IssuedTokenRequestDTO $issuedTokenRequest,
    ): void {
        $this->dispatcher->dispatch(
            new UserRegisteredEvent($issuedTokenRequest)
        );
    }
}
