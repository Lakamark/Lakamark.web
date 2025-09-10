<?php
/**
 * This provider is for to hash the passwords in the database when you use Alice to load your fixtures.
 * If you use the default provider <password('your_password')> in your fixtures.
 * The default password provider hash not well the passwords. I have some issue when I'm testing my application
 * When the authenticator tried to identifier a user with his hashed password in the database.
 *
 * On the doc they are a chapter about to create your own provider:
 *
 * @doc : https://github.com/nelmio/alice/blob/main/doc/customizing-data-generation.md#custom-faker-data-providers
 *
 * I used UserPasswordHasherInterface from Symfony to hash my passwords.
 * Like when your register a new user or edit a password in your application
 * I mapped the (security.user_password_hasher) available in the framework to handle it.
 *
 * In your service.yml you should manually register your custom provider
 * with the arguments (@security.user_password_hasher) and to tag 'nelmio_alice.faker.provider'
 * Alice load your custom provider.
 */

namespace App\Foundation\Faker\Provider;

use App\Domain\Auth\Entity\User;
use Faker\Generator;
use Faker\Provider\Base as BaseProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class PasswordProvider extends BaseProvider
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        Generator $generator,
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
    public function hashPassword(User $user, string $plainPassword): string
    {
        return $this->hasher->hashPassword($user, $plainPassword);
    }
}
