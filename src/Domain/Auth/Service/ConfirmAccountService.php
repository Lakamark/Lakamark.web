<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\DTO\ConfirmUserResultDTO;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;

readonly class ConfirmAccountService
{
    public function __construct(
        private TokenRequestService $tokenRequestService,
        private UserRoleManagerService $roleManagerService,
        private EntityManagerInterface $em,
        private ClockInterface $clock,
    ) {
    }

    public function confirm(
        string $rawToken,
    ): ConfirmUserResultDTO {
        $tokenRequest = $this->tokenRequestService->consume(
            rawToken: $rawToken,
            type: TokenRequestType::REGISTER_CONFIRMATION,
        );

        $user = $tokenRequest->getUser();

        $user->confirmEmail($this->clock->now());

        $this->roleManagerService->grant($user, UserRole::USER_VERIFIED);

        $this->em->flush();

        return new ConfirmUserResultDTO(
            user: $user,
            emailConfirmed: true,
            roleVerifiedAdded: true,
        );
    }
}
