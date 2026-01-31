<?php

namespace App\Domain\Moderation\Event;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;

readonly class UnbannedUserEvent
{
    public function __construct(
        private User $user,
        private UserBan $ban,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getBan(): UserBan
    {
        return $this->ban;
    }
}
