<?php

namespace App\Tests\Http\Controller;

use App\Tests\FixturesLoaderTrait;
use App\Tests\WebTestCase;

class ResendConfirmationControllerTest extends WebTestCase
{
    use FixturesLoaderTrait;

    private const string RESENT_CONFIRMATION_URI = '/account/confirmation/resend';

    public function testResendConfirmationForUnconfirmedUser(): void
    {
        $fixtures = $this->loadFixtures(['users']);

        $user = $fixtures['user_confirmed'];

        $this->client->loginUser($user);

        $this->client->request('POST', self::RESENT_CONFIRMATION_URI);

        $this->assertResponseRedirects('/account');

        $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert-info', 'Your email is already confirmed.');
    }

    public function testResendRequiresAuthentication(): void
    {
        $this->client->request('POST', self::RESENT_CONFIRMATION_URI);

        $this->assertResponseRedirects('/login');
    }
}
