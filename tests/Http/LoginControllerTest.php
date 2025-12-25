<?php

namespace App\Tests\Http;

use App\Domain\Auth\Entity\User;
use App\Tests\FixturesLoaderTrait;
use App\Tests\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    use FixturesLoaderTrait;

    private const string LOGIN_BUTTON = 'Sign in';
    private const string LOGIN_PATH = '/login';

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

    public function testFailLogin(): void
    {
        /** @var User $user */
        ['user1' => $user] = $this->loadFixtures(['users']);
        $crawler = $this->client->request('GET', self::LOGIN_PATH);
        $form = $crawler->selectButton(self::LOGIN_BUTTON)->form();
        $form->setValues([
            '_username' => $user->getUsername(),
            '_password' => '1234567890',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects(self::LOGIN_PATH);
        $this->client->followRedirect();
    }
}
