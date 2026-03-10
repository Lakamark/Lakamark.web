<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\OAuthProvider;
use Symfony\Component\HttpFoundation\Request;

class BeforeUserRegisterEvent
{
    public function __construct(
        public User $user,
        public Request $request,
        public OAuthProvider $authProvider,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getAuthProvider(): OAuthProvider
    {
        return $this->authProvider;
    }

    public function isLocalRegistration(): bool
    {
        return OAuthProvider::LOCAL === $this->authProvider;
    }

    public function isOauthRegistration(): bool
    {
        return OAuthProvider::LOCAL !== $this->authProvider;
    }
}
