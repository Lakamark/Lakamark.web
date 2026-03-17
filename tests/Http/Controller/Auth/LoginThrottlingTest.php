<?php

declare(strict_types=1);

namespace App\Tests\Http\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LoginThrottlingTest extends WebTestCase
{
    private const string LOGIN_PATH = '/login';
    private const string VALID_EMAIL = 'user@example.com';
    private const string UNKNOWN_EMAIL = 'missing@example.com';
    private const string VALID_PASSWORD = 'Password123!';
    private const string INVALID_PASSWORD = 'wrong-password';

    protected function setUp(): void
    {
        parent::setUp();

        $this->shutdownKernel();
    }

    public function testItAllowsLoginWithValidCredentialsBeforeThreshold(): void
    {
        $client = $this->createBrowser('123.123.123.10');

        $this->submitLogin($client, self::VALID_EMAIL, self::VALID_PASSWORD);

        $this->assertTrue(
            $client->getResponse()->isRedirect(),
            'Expected successful login to redirect the user.'
        );
    }

    public function testItBlocksLoginAfterRepeatedFailuresForSameEmailAndIp(): void
    {
        $client = $this->createBrowser('123.123.123.11');

        for ($i = 0; $i < 6; ++$i) {
            $this->submitLogin($client, self::VALID_EMAIL, self::INVALID_PASSWORD);
        }

        $this->assertTrue($client->getResponse()->isRedirect('/login'));

        $client->followRedirect();

        $this->assertSelectorExists('[data-test="auth-error"]');
    }

    public function testItBlocksLoginAfterMultipleFailuresFromSameIp(): void
    {
        $client = $this->createBrowser('123.123.123.12');

        for ($i = 0; $i < 30; ++$i) {
            $this->submitLogin($client, sprintf('user%d@example.com', $i), self::INVALID_PASSWORD);
        }

        $this->assertTrue($client->getResponse()->isRedirect('/login'));

        $client->followRedirect();

        $this->assertSelectorExists('[data-test="auth-error"]');
    }

    public function testItDoesNotRevealIfAccountExists(): void
    {
        $existingClient = $this->createBrowser('123.123.123.13');
        $this->submitLogin($existingClient, self::VALID_EMAIL, self::INVALID_PASSWORD);

        $existingResponse = $existingClient->getResponse();
        $existingStatus = $existingResponse->getStatusCode();
        $existingContent = $this->normalizeHtml($existingResponse->getContent());

        $this->shutdownKernel();

        $unknownClient = $this->createBrowser('123.123.123.14');
        $this->submitLogin($unknownClient, self::UNKNOWN_EMAIL, self::INVALID_PASSWORD);

        $unknownResponse = $unknownClient->getResponse();
        $unknownStatus = $unknownResponse->getStatusCode();
        $unknownContent = $this->normalizeHtml($unknownResponse->getContent());

        $this->assertSame($existingStatus, $unknownStatus);
        $this->assertSame($existingContent, $unknownContent);
    }

    private function createBrowser(string $ip): KernelBrowser
    {
        return LoginThrottlingTest::createClient(server: [
            'REMOTE_ADDR' => $ip,
        ]);
    }

    private function submitLogin(KernelBrowser $client, string $username, string $password): void
    {
        $crawler = $client->request('GET', self::LOGIN_PATH);
        $form = $crawler->selectButton('Login')->form();

        $form['username'] = $username;
        $form['password'] = $password;
        $form['_csrf_token'] = 'test';

        $client->submit($form);
    }

    private function normalizeHtml(string $content): string
    {
        $content = strip_tags($content);
        $content = preg_replace('/\s+/', ' ', $content) ?? $content;

        return trim(mb_strtolower($content));
    }

    private function shutdownKernel(): void
    {
        LoginThrottlingTest::ensureKernelShutdown();
    }
}
