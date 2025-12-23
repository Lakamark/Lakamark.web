<?php

namespace App\Foundation\Provider;

use App\Domain\Auth\Entity\User;
use Faker\Generator;
use Faker\Provider\Base as BaseProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class hashedPassword extends BaseProvider
{
    public function __construct(
        Generator $generator,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct($generator);
    }

    /**
     * To hash a password.
     * To use this provider in your fixtures:
     *
     * @self is the current object being created (your User entity).
     *
     * App\Entity\User:
     *      user_{1..10}:
     *      email: 'user<current()>@example.com'
     *      password: <hashPassword(@self, 'secret')>
     */
    public function hashedPassword(User $user, string $plainPassword): string
    {
        return $this->hasher->hashPassword($user, $plainPassword);
    }
}
