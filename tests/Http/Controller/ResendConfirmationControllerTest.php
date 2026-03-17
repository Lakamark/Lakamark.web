<?php

namespace App\Tests\Http\Controller;

use App\Tests\FixturesLoaderTrait;
use App\Tests\WebTestCase;

final class ResendConfirmationControllerTest extends WebTestCase
{
    use FixturesLoaderTrait;

    private const string RESENT_CONFIRMATION_URI = '/account/confirmation/resend';
    private const string ACCOUNT_URI = '/account';
    private const string BUTTON_LABEL = 'Resend confirmation email';

    protected function setUp(): void
    {
        parent::setUp();

        ResendConfirmationControllerTest::getContainer()->get('cache.rate_limiter')->clear();
    }

    public function testConfirmedUserDoesNotSeeResendConfirmationButton(): void
    {
        $fixtures = $this->loadFixtures(['users']);
        $user = $fixtures['user_confirmed'];

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', self::ACCOUNT_URI);

        self::assertCount(0, $crawler->selectButton(self::BUTTON_LABEL));
    }

    public function testUnconfirmedUserSeesResendConfirmationButton(): void
    {
        $fixtures = $this->loadFixtures(['users']);
        $user = $fixtures['user_unconfirmed'];

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', self::ACCOUNT_URI);

        self::assertCount(1, $crawler->selectButton(self::BUTTON_LABEL));
    }

    public function testResendConfirmationForUnconfirmedUser(): void
    {
        $fixtures = $this->loadFixtures(['users']);
        $user = $fixtures['user_unconfirmed'];

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', self::ACCOUNT_URI);
        $this->client->submit(
            $crawler->selectButton(self::BUTTON_LABEL)->form()
        );

        $this->assertResponseRedirects(self::ACCOUNT_URI);
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success');
        $this->assertStringContainsString(
            'confirmation email',
            trim($this->client->getCrawler()->filter('.alert-success')->text())
        );
    }

    public function testResendRequiresAuthentication(): void
    {
        $this->client->request('POST', self::RESENT_CONFIRMATION_URI);

        $this->assertResponseRedirects('/login');
    }

    public function testResendConfirmationIsRateLimited(): void
    {
        $fixtures = $this->loadFixtures(['users']);
        $user = $fixtures['user_unconfirmed'];

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', self::ACCOUNT_URI);
        $this->client->submit(
            $crawler->selectButton(self::BUTTON_LABEL)->form()
        );
        $this->assertResponseRedirects(self::ACCOUNT_URI);
        $this->client->followRedirect();

        $crawler = $this->client->request('GET', self::ACCOUNT_URI);
        $this->client->submit(
            $crawler->selectButton(self::BUTTON_LABEL)->form()
        );
        $this->assertResponseRedirects(self::ACCOUNT_URI);
        $this->client->followRedirect();

        $crawler = $this->client->request('GET', self::ACCOUNT_URI);
        $this->client->submit(
            $crawler->selectButton(self::BUTTON_LABEL)->form()
        );
        $this->assertResponseRedirects(self::ACCOUNT_URI);
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-error');
        $this->assertStringContainsString(
            'Too many confirmation email requests',
            trim($this->client->getCrawler()->filter('.alert-error')->text())
        );
    }

    public function testResendRejectsInvalidCsrfToken(): void
    {
        $fixtures = $this->loadFixtures(['users']);
        $user = $fixtures['user_unconfirmed'];

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', self::ACCOUNT_URI);
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton(self::BUTTON_LABEL)->form();
        $form['_csrf_token'] = 'invalid-token';

        $this->client->submit($form);

        $this->assertResponseRedirects('/login');
    }
}
