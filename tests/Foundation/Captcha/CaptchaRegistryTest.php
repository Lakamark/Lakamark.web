<?php

namespace App\Tests\Foundation\Captcha;

use App\Foundation\Captcha\CaptchaRegistry;
use App\Foundation\Captcha\Contract\CaptchaChallengeInterface;
use App\Foundation\Captcha\Contract\CaptchaGeneratorInterface;
use App\Foundation\Captcha\Exception\CaptchaInvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CaptchaRegistryTest extends TestCase
{
    public function testReturnsChallengeForKnownType(): void
    {
        $challenge = $this->createStub(CaptchaChallengeInterface::class);
        $generator = $this->createStub(CaptchaGeneratorInterface::class);

        $registry = new CaptchaRegistry(
            ['puzzle' => $challenge],
            ['puzzle' => $generator],
        );

        $this->assertSame($challenge, $registry->challenge('puzzle'));
    }

    public function testReturnsGeneratorForKnownType(): void
    {
        $challenge = $this->createStub(CaptchaChallengeInterface::class);
        $generator = $this->createStub(CaptchaGeneratorInterface::class);

        $registry = new CaptchaRegistry(
            ['puzzle' => $challenge],
            ['puzzle' => $generator],
        );
        $this->assertSame($generator, $registry->generator('puzzle'));
    }

    public function testChallengeThrowsExceptionForUnknownType(): void
    {
        $registry = new CaptchaRegistry([], []);

        $this->expectException(CaptchaInvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown captcha type: unknown');

        $registry->challenge('unknown');
    }
}
