<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\UserRole;

final readonly class UserRoleManagerService
{
    public function grant(
        User $user,
        UserRole ...$roles,
    ): void {
        $current = $user->getRoles();

        foreach ($roles as $role) {
            $value = $role->value;
            if (!\in_array($value, $current, true)) {
                $current[] = $value;
            }
        }

        $user->setRoles(\array_values(\array_unique($current)));
    }

    public function revoke(
        User $user,
        UserRole ...$roles,
    ): void {
        $remove = \array_map(static fn (UserRole $r) => $r->value, $roles);
        $current = $user->getRoles();

        $user->setRoles(\array_values(\array_diff($current, $remove)));
    }

    public function has(User $user, UserRole $role): bool
    {
        return \in_array($role->value, $user->getRoles(), true);
    }

    /*
     * Policy: A verified user must have ROLE_USER and ROLE_USER_VERIFIED
     */
    public function markVerified(User $user): void
    {
        $this->grant($user, UserRole::USER, UserRole::USER_VERIFIED);
    }

    public function unverify(User $user): void
    {
        $this->revoke($user, UserRole::USER_VERIFIED);
        // Keep the  default role USER_ROLE.
    }
}
