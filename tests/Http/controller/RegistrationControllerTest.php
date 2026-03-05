<?php

namespace App\Tests\Http\controller;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Service\TokenRequestService;
use App\Tests\FixturesLoaderTrait;
use App\Tests\WebTestCase;
use Random\RandomException;

class RegistrationControllerTest extends WebTestCase
{
    use FixturesLoaderTrait;

    private const string SIGNUP_PATH = '/register';
    private const string CONFIRM_PATH = '/register/confirmation';
    private const string SIGNUP_BTN = 'Register';

    public function testGetTheRegisterPage(): void
    {
        $crawler = $this->client->request('GET', self::SIGNUP_PATH);
        $this->assertResponseStatusCodeSame(200);
        $content = $crawler->filter('.h1')->text();
        $this->assertEquals('Register', $content);
    }

    public function testSuccessRegistration(): void
    {
        $crawler = $this->client->request('GET', self::SIGNUP_PATH);
        $form = $crawler->selectButton(self::SIGNUP_BTN)->form();
        $form->setValues([
            'registration_form' => [
                'username' => 'John Doe',
                'email' => 'john@do.com',
                'plainPassword' => 'password',
            ],
        ]);
        $this->client->submit($form);
        $this->expectedFormErrors(0);
        $this->assertEmailCount(1);
        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
    }

    public function testAlreadyRegisteredAccountWithEmail(): void
    {
        /** @var array<string,User> $users */
        $users = $this->loadFixtures(['users']);
        $crawler = $this->client->request('GET', self::SIGNUP_PATH);
        $form = $crawler->selectButton(self::SIGNUP_BTN)->form();
        $fomData = [
            'registration_form' => [
                'username' => 'John Doe',
                'email' => $users['user1']->getEmail(),
                'plainPassword' => 'password',
            ],
        ];

        $form->setValues($fomData);
        $this->client->submit($form);
        $this->expectedFormErrors(1);
        $this->assertEmailCount(0);
    }

    public function testAlreadyRegisteredAccountWithUsername(): void
    {
        /** @var array<string,User> $users */
        $users = $this->loadFixtures(['users']);
        $crawler = $this->client->request('GET', self::SIGNUP_PATH);
        $form = $crawler->selectButton(self::SIGNUP_BTN)->form();
        $formData = [
            'registration_form' => [
                'username' => $users['user1']->getUsername(),
                'email' => 'john@do.com',
                'plainPassword' => 'password',
            ],
        ];
        $form->setValues($formData);
        $this->client->submit($form);
        $this->expectedFormErrors(1);
        $this->assertEmailCount(0);
    }

    public function testWithAnEmailTooLonger(): void
    {
        $this->loadFixtures(['users']);
        $crawler = $this->client->request('GET', self::SIGNUP_PATH);
        $form = $crawler->selectButton(self::SIGNUP_BTN)->form();
        $formData = [
            'registration_form' => [
                'username' => 'John Doe',
                'email' => 'i-bought-a_ponymadeof_diamonds-because-im-rich_you-wannaknow_what-i-named_it_butt_stallion_you_know_this_ref@do.com',
                'plainPassword' => 'password',
            ],
        ];
        $form->setValues($formData);
        $this->client->submit($form);
        $this->expectedFormErrors(1);
        $this->assertEmailCount(0);
    }

    public function testInvalidConfirmationToken(): void
    {
        /** @var User[] $users */
        $users = $this->loadFixtures(['users']);

        $this->client->request(
            'GET',
            self::CONFIRM_PATH.'?token=faketoken'
        );

        $this->assertResponseRedirects(self::SIGNUP_PATH);
        $this->client->followRedirect();
    }

    /**
     * @throws RandomException
     */
    public function testValidConfirmationToken(): void
    {
        /** @var array<string,User> $users */
        $users = $this->loadFixtures(['users']);
        $user = $users['user_unconfirmed'];

        $uri = $this->makeConfirmationUri($user);

        $this->client->request('GET', $uri);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    /**
     * @throws RandomException
     */
    public function testUseExpiredConfirmationToken(): void
    {
        /** @var array<string,User> $users */
        $users = $this->loadFixtures(['users']);
        $user = $users['user_unconfirmed'];

        // We pass the oldest token
        $past = new \DateTimeImmutable('-10 days');
        $uri = $this->makeConfirmationUri($user, $past);

        $this->client->request('GET', $uri);

        $this->assertResponseRedirects(self::SIGNUP_PATH);
        $this->client->followRedirect();
    }

    public function testMissingTokenQueryParameter(): void
    {
        $this->loadFixtures(['users']);

        $this->client->request('GET', self::CONFIRM_PATH);

        $this->assertResponseRedirects(self::SIGNUP_PATH);
    }

    public function testUserAlreadyLoggedIn(): void
    {
        /** @var User[] $users */
        $users = $this->loadFixtures(['users']);
        $user = $users['user1'];
        $this->login($user);
        $this->client->request('GET', self::SIGNUP_PATH);

        $this->assertResponseRedirects('/account');
    }

    /**
     * To prepare a confirmation token request.
     *
     * @throws RandomException
     */
    private function makeConfirmationUri(User $users, ?\DateTimeImmutable $now = null): string
    {
        $now ?: new \DateTimeImmutable();

        /** @var TokenRequestService $service */
        $service = self::getContainer()->get(TokenRequestService::class);

        $issued = $service->issue($users, TokenRequestType::REGISTER_CONFIRMATION, $now);

        return self::CONFIRM_PATH.'?token='.$issued->issued->token;
    }
}
