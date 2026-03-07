<?php

namespace App\Http\Security\Voter;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\UserAccess;
use App\Domain\Auth\Security\UserAccessPolicy;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserStatusVoter extends Voter
{
    public const string ROLE_USER_VERIFIED = 'ROLE_USER_VERIFIED';
    public const string ROLE_USER_BANNED = 'ROLE_USER_BANNED';

    public function __construct(
        private readonly UserAccessPolicy $policy,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::ROLE_USER_VERIFIED,
            self::ROLE_USER_BANNED,
        ], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        $now = new \DateTimeImmutable();

        return match ($attribute) {
            self::ROLE_USER_VERIFIED => $this->policy->has($user, UserAccess::VERIFIED),
            self::ROLE_USER_BANNED => $this->policy->has($user, UserAccess::NOT_BANNED),
            default => false,
        };
    }
}
