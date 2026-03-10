<?php

namespace App\Tests\Foundation\Captcha;

use App\Foundation\Captcha\CaptchaService;
use App\Foundation\Captcha\Contract\CaptchaChallengeInterface;
use App\Foundation\Captcha\Contract\CaptchaGeneratorInterface;
use App\Foundation\Captcha\Contract\CaptchaRegistryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class CaptchaServiceTest extends TestCase
{
    private CaptchaRegistryInterface|MockObject $registry;
    private CaptchaChallengeInterface|MockObject $challenge;
    private CaptchaGeneratorInterface|MockObject $generator;

    private Session $session;
    private RequestStack $requestStack;
    private MockClock $clock;

    private CaptchaService $service;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(CaptchaRegistryInterface::class);
        $this->challenge = $this->createMock(CaptchaChallengeInterface::class);
        $this->generator = $this->createStub(CaptchaGeneratorInterface::class);

        $this->session = new Session(new MockArraySessionStorage());

        $request = new Request();
        $request->setSession($this->session);

        $this->requestStack = new RequestStack();
        $this->requestStack->push($request);

        $this->clock = new MockClock('2026-03-07 12:00:00');

        $this->service = new CaptchaService(
            $this->registry,
            $this->requestStack,
            $this->clock,
        );
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function testVerifyReturnsTrueWhenAnswerIsValid(): void
    {
        $this->seedCaptchaSession();
        $this->clock->modify('+3 seconds');

        $this->challenge
            ->expects($this->once())
            ->method('verify')
            ->with('abc123', '42')
            ->willReturn(true);

        $this->registry
            ->expects($this->once())
            ->method('challenge')
            ->with('image')
            ->willReturn($this->challenge);

        $this->assertTrue($this->service->verify('image', '42'));
        $this->assertSame(0, $this->session->get('CAPTCHA_TRIES'));
        $this->assertSame('abc123', $this->session->get('CAPTCHA_KEY'));
        $this->assertSame('image', $this->session->get('CAPTCHA_TYPE'));
        $this->assertNotNull($this->session->get('CAPTCHA_GENERATED_AT'));
        $this->assertTrue($this->session->get('CAPTCHA_VERIFIED'));
        $this->assertSame('image', $this->session->get('CAPTCHA_VERIFIED_TYPE'));
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function testVerifyReturnsFalseWhenAnswerIsInvalid(): void
    {
        $this->seedCaptchaSession();
        $this->clock->modify('+3 seconds');

        $this->challenge
            ->expects($this->once())
            ->method('verify')
            ->with('abc123', 'wrong')
            ->willReturn(false);

        $this->registry
            ->expects($this->once())
            ->method('challenge')
            ->with('image')
            ->willReturn($this->challenge);

        $this->assertFalse($this->service->verify('image', 'wrong'));
        $this->assertSame(1, $this->session->get('CAPTCHA_TRIES'));
    }

    public function testVerifyReturnsFalseWhenSolvedTooFast(): void
    {
        $this->seedCaptchaSession();

        $this->challenge
            ->expects($this->never())
            ->method('verify');

        $this->registry
            ->expects($this->never())
            ->method('challenge');

        $this->assertFalse($this->service->verify('image', '42'));
        $this->assertSame(1, $this->session->get('CAPTCHA_TRIES'));
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function testVerifyReturnsFalseWhenCaptchaExpired(): void
    {
        $this->seedCaptchaSession();
        $this->clock->modify('+6 minutes');

        $this->challenge
            ->expects($this->never())
            ->method('verify');

        $this->registry
            ->expects($this->never())
            ->method('challenge');

        $this->assertFalse($this->service->verify('image', '42'));
        $this->assertSame(1, $this->session->get('CAPTCHA_TRIES'));
    }

    private function seedCaptchaSession(): void
    {
        $this->session->set('CAPTCHA_KEY', 'abc123');
        $this->session->set('CAPTCHA_TYPE', 'image');
        $this->session->set('CAPTCHA_GENERATED_AT', $this->clock->now()->getTimestamp());
    }
}
