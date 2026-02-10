<?php

namespace App\Tests\Foundation\Captcha;

use App\Foundation\Captcha\CaptchaService;
use App\Foundation\Captcha\Contract\CaptchaChallengeInterface;
use App\Foundation\Captcha\Contract\CaptchaGeneratorInterface;
use App\Foundation\Captcha\Contract\CaptchaRegistryInterface;
use App\Foundation\Captcha\Exception\CaptchaLockedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class CaptchaServiceTest extends TestCase
{
    private const string CAPTCHA_TEST_TYPE = 'puzzle';
    private const string CAPTCHA_TEST_KEY_SESSION = 'CAPTCHA_KEY';
    private const string CAPTCHA_TEST_TYPE_SESSION = 'CAPTCHA_TYPE';
    private const string CAPTCHA_TRIES_SESSION = 'CAPTCHA_TRIES';
    private const string CAPTCHA_TEST_ID = 'K451';

    public function testGenerateStoresKeyTypeAndReturnsResponse(): void
    {
        $session = $this->createSession();
        $stack = $this->createRequestStackWithSession($session);

        $registry = $this->createMock(CaptchaRegistryInterface::class);
        $challenge = $this->createMock(CaptchaChallengeInterface::class);
        $generator = $this->createMock(CaptchaGeneratorInterface::class);

        $registry->expects($this->once())
            ->method('challenge')
            ->with(self::CAPTCHA_TEST_TYPE)
            ->willReturn($challenge);

        $registry->expects($this->once())
            ->method('generator')
            ->with(self::CAPTCHA_TEST_TYPE)
            ->willReturn($generator);

        $challenge->expects($this->once())
            ->method('generateKey')
            ->willReturn(self::CAPTCHA_TEST_ID);

        $generator->expects($this->once())
            ->method('generate')
            ->with(self::CAPTCHA_TEST_ID)
            ->willReturn(new Response('ok', 200));

        $service = new CaptchaService($registry, $stack);

        $response = $service->generate(self::CAPTCHA_TEST_TYPE);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(self::CAPTCHA_TEST_ID, $session->get(self::CAPTCHA_TEST_KEY_SESSION));
        $this->assertSame(self::CAPTCHA_TEST_TYPE, $session->get(self::CAPTCHA_TEST_TYPE_SESSION));
        $this->assertSame(0, $session->get(self::CAPTCHA_TRIES_SESSION, 0));
    }

    public function testVerifyReturnsFalseWhenNoTypeInSession(): void
    {
        $session = $this->createSession();
        $stack = $this->createRequestStackWithSession($session);

        $registry = $this->createMock(CaptchaRegistryInterface::class);
        $registry->expects($this->never())->method('challenge');

        $service = new CaptchaService($registry, $stack);

        $this->assertFalse($service->verify('answer'));
    }

    public function testVerifyIncrementsTriesOnFailure(): void
    {
        $session = $this->createSession();
        $session->set(self::CAPTCHA_TEST_TYPE_SESSION, self::CAPTCHA_TEST_TYPE);
        $session->set(self::CAPTCHA_TEST_KEY_SESSION, self::CAPTCHA_TEST_ID);
        $session->set(self::CAPTCHA_TRIES_SESSION, 0);

        $stack = $this->createRequestStackWithSession($session);

        $registry = $this->createMock(CaptchaRegistryInterface::class);
        $challenge = $this->createMock(CaptchaChallengeInterface::class);

        $registry->expects($this->once())
            ->method('challenge')
            ->with(self::CAPTCHA_TEST_TYPE)
            ->willReturn($challenge);

        $challenge->expects($this->once())
            ->method('verify')
            ->with(self::CAPTCHA_TEST_ID, 'bad')
            ->willReturn(false);

        $service = new CaptchaService($registry, $stack);

        $this->assertFalse($service->verify('bad'));
        $this->assertSame(1, $session->get(self::CAPTCHA_TRIES_SESSION));
    }

    public function testVerifyResetsTriesOnSuccess(): void
    {
        $session = $this->createSession();
        $session->set(self::CAPTCHA_TEST_TYPE_SESSION, self::CAPTCHA_TEST_TYPE);
        $session->set(self::CAPTCHA_TEST_KEY_SESSION, self::CAPTCHA_TEST_ID);
        $session->set(self::CAPTCHA_TRIES_SESSION, 2);

        $stack = $this->createRequestStackWithSession($session);

        $registry = $this->createMock(CaptchaRegistryInterface::class);
        $challenge = $this->createMock(CaptchaChallengeInterface::class);

        $registry->expects($this->once())
            ->method('challenge')
            ->with(self::CAPTCHA_TEST_TYPE)
            ->willReturn($challenge);

        $challenge->expects($this->once())
            ->method('verify')
            ->with(self::CAPTCHA_TEST_ID, 'good')
            ->willReturn(true);

        $service = new CaptchaService($registry, $stack);

        $this->assertTrue($service->verify('good'));
        $this->assertSame(0, $session->get(self::CAPTCHA_TRIES_SESSION));
    }

    public function testVerifyThrowsWhenLocked(): void
    {
        $session = $this->createSession();
        $session->set(self::CAPTCHA_TEST_TYPE_SESSION, self::CAPTCHA_TEST_TYPE);
        $session->set(self::CAPTCHA_TEST_KEY_SESSION, self::CAPTCHA_TEST_ID);
        $session->set(self::CAPTCHA_TRIES_SESSION, 3);

        $stack = $this->createRequestStackWithSession($session);

        $registry = $this->createMock(CaptchaRegistryInterface::class);
        $registry->expects($this->never())->method('challenge');

        $service = new CaptchaService($registry, $stack);

        $this->expectException(CaptchaLockedException::class);
        $service->verify('anything');
    }

    private function createSession(): Session
    {
        return new Session(new MockArraySessionStorage());
    }

    private function createRequestStackWithSession(Session $session): RequestStack
    {
        $request = new Request();
        $request->setSession($session);

        $stack = new RequestStack();
        $stack->push($request);

        return $stack;
    }
}
