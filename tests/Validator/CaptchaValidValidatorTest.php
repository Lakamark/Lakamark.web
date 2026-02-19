<?php

namespace App\Tests\Validator;

use App\Foundation\Captcha\Contract\CaptchaVerifierInterface;
use App\Foundation\Captcha\Exception\CaptchaLockedException;
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

    public function testDoesNothingOnEmptyValue(): void
    {
        $this->captcha->expects($this->never())->method('verify');
        $this->validator->validate('', new CaptchaValid());
        $this->assertNoViolation();
    }

    public function testItAddsViolationWhenInvalid(): void
    {
        $this->captcha
            ->expects($this->once())
            ->method('verify')
            ->with(null, 'abc')
            ->willReturn(false);

        $constraint = new CaptchaValid(message: 'Captcha invalid.');
        $this->validator->validate(' abc ', $constraint);

        $this
            ->buildViolation('Captcha invalid.')
            ->assertRaised();
    }

    public function testItAddsLockedViolationWhenLocked(): void
    {
        $this->captcha
            ->expects($this->once())
            ->method('verify')
            ->with(null, 'abc')
            ->willThrowException(new CaptchaLockedException());

        $constraint = new CaptchaValid(lockedMessage: 'The captcha is locked.');
        $this->validator->validate('abc', $constraint);

        $this
            ->buildViolation('The captcha is locked.')
            ->assertRaised();
    }

    public function testItCanForceASpecificTypeIfProvided(): void
    {
        $this->captcha
            ->expects($this->once())
            ->method('verify')
            ->with('math', '123')
            ->willReturn(true);

        $this->validator->validate('123', new CaptchaValid(type: 'math'));
        $this->assertNoViolation();
    }
}
