<?php

namespace App\Tests\Validator;

use App\Foundation\Captcha\Contract\CaptchaVerifierInterface;
use App\Validator\CaptchaValid;
use App\Validator\CaptchaValidValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CaptchaValidValidatorTest extends ConstraintValidatorTestCase
{
    private CaptchaVerifierInterface|MockObject $captcha;

    protected function createValidator(): CaptchaValidValidator
    {
        $this->captcha = $this->createMock(CaptchaVerifierInterface::class);

        return new CaptchaValidValidator($this->captcha);
    }

    public function testItAddsViolationWhenInvalid(): void
    {
        $this->captcha
            ->expects($this->once())
            ->method('consumeVerified')
            ->with(null)
            ->willReturn(false);

        $constraint = new CaptchaValid(message: 'Captcha invalid.');
        $this->validator->validate(' abc ', $constraint);

        $this
            ->buildViolation('Captcha invalid.')
            ->assertRaised();
    }

    public function testItPassesWhenCaptchaWasPreviouslyVerified(): void
    {
        $this->captcha
            ->expects($this->once())
            ->method('consumeVerified')
            ->with(null)
            ->willReturn(true);

        $this->validator->validate('abc', new CaptchaValid());
        $this->assertNoViolation();
    }

    public function testItCanForceASpecificTypeIfProvided(): void
    {
        $this->captcha
            ->expects($this->once())
            ->method('consumeVerified')
            ->with('math')
            ->willReturn(true);

        $this->validator->validate('123', new CaptchaValid(type: 'math'));

        $this->assertNoViolation();
    }
}
