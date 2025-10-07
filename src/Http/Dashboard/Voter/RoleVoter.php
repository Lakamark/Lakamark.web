<?php

namespace App\Http\Dashboard\Voter;

use App\Domain\Auth\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RoleVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return 'IS_IMPERSONATOR' != $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // TODO: Change the role by authorised roles like ADMIN, EDITOR!
        return in_array('ROLE_USER', $user->getRoles());
    }
}
