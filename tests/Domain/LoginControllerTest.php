<?php

namespace App\Tests\Domain;

use App\Domain\Auth\Entity\User;
use App\Tests\FixturesLoaderTrait;
use App\Tests\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    use FixturesLoaderTrait;


    private const LOGIN_BUTTON = 'Sign in';
    private const LOGIN_PATH = '/login';

    public function testSuccessLogin(): void
    {
        /** @var User $user */
        ['user1' => $user] = $this->loadFixtures(['users']);
        $crawler = $this->client->request('GET', self::LOGIN_PATH);
        $form = $crawler->selectButton(self::LOGIN_BUTTON)->form();
        $form->setValues([
            '_username' => $user->getUsername(),
            '_password' => '0000',
        ]);
        $this->client->submit($form);
        $this->expectedFormErrors(0);
    }
}
