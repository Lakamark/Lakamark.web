<?php

namespace App\Tests;

use App\Domain\Auth\Repository\TokenRequestRepository;
use App\Domain\Auth\Repository\UserRepository;

abstract class AuthKernelTestCase extends DomainServiceTestCase
{
    protected UserRepository $userRepository;
    protected TokenRequestRepository $tokenRequestRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->service(UserRepository::class);
        $this->tokenRequestRepository = $this->service(TokenRequestRepository::class);
    }
}
