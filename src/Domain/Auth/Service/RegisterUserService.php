<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\DTO\RegisterUserResultDTO;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Event\BeforeUserRegisterEvent;
use App\Domain\Auth\Event\UserRegisteredEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class RegisterUserService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em,
        private TokenRequestService $tokenRequestService,
        private EventDispatcherInterface $dispatcher,
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
            ->setCreatedAt(new \DateTimeImmutable());

        $this->dispatcher->dispatch(new BeforeUserRegisterEvent($user, $request));

        $this->em->persist($user);
        $this->em->flush();

        $issuedTokenRequest = null;

        // If the user signup with via the RegistrationForm
        if (!$isOauthRequest) {
            $issuedTokenRequest = $this->tokenRequestService->issue(
                user: $user,
                type: TokenRequestType::REGISTER_CONFIRMATION,
            );

            // Dispatch UserRegisteredEvent.
            $this->dispatcher->dispatch(
                new UserRegisteredEvent($issuedTokenRequest)
            );
        }

        return new RegisterUserResultDTO(
            user: $user,
            isOauthRequest: $isOauthRequest,
            issuedTokenRequest: $issuedTokenRequest,
        );
    }
}
