<?php

namespace App\Http\Security\Voter;

use App\Domain\Auth\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class VerifiedUserVoter extends Voter
{
    public const string VERIFIED = 'ROLE_USER_VERIFIED';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::VERIFIED === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return $user->isEmailConfirmed();
    }
}
