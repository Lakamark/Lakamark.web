<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Entity\User;
use Symfony\Component\HttpFoundation\Request;

readonly class BeforeUserRegisterEvent
{
    public function __construct(
        public User $user,
        public Request $request,
    ) {
    }
}
