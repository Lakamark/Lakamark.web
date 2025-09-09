<?php

namespace App\Tests\Http\Controller;

use App\Domain\Auth\Entity\User;
use App\Tests\FixturesLoaderTrait;
use App\Tests\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    use FixturesLoaderTrait;

    private const SIGNUP_PATH = '/register';
    private const CONFIRM_PATH = '/register/confirmation/';
    private const SIGNUP_BTN = 'Register';

    public function testDisplayRegistrationPage(): void
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
        $formData = [
            'registration_form' => [
                'username' => 'John Doe',
                'email' => $users['user1']->getEmail(),
                'plainPassword' => 'password',
            ],
        ];
        $form->setValues($formData);
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
            $this->makeConfirmationToken($users)
        );

        $this->assertResponseRedirects(self::SIGNUP_PATH);
        $this->client->followRedirect();
    }

    public function testValidConfirmationToken(): void
    {
        /** @var User[] $users */
        $users = $this->loadFixtures(['users']);
        $user = $users['user_unconfirmed'];
        $this->client->request(
            'GET',
            $this->makeConfirmationToken($users, $user->getConfirmationToken())
        );

        $this->client->followRedirect();
    }

    public function testUseExpiredConfirmationToken(): void
    {
        /** @var User[] $users */
        $users = $this->loadFixtures(['users']);
        $user = $users['user_unconfirmed'];
        $user->setCreatedAt(new \DateTimeImmutable('-1 day'));
        $this->em->flush();

        $this->client->request(
            'GET',
            $this->makeConfirmationToken($users, $user->getConfirmationToken())
        );

        $this->assertResponseRedirects(self::SIGNUP_PATH);
        $this->client->followRedirect();
    }

    public function testRedirectUserAlreadyLoggedIn(): void
    {
        $this->loadFixtures(['users']);
        $this->markTestIncomplete('TODO: Write the test.');
    }

    /**
     * To prepare a confirmation token request.
     */
    private function makeConfirmationToken($users, ?string $token = null): string
    {
        /** @var User[] $users */
        $user = $users['user_unconfirmed'];
        if (null === $token) {
            $token = 'faketoken';
        } else {
            $token = $user->getConfirmationToken();
        }

        return self::CONFIRM_PATH.$user->getId().'?confirmation_token='.$token;
    }
}
