<?php

namespace App\Validator;

use App\Foundation\Captcha\CaptchaService;
use App\Foundation\Captcha\Exception\CaptchaLockedException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CaptchaValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly CaptchaService $captcha,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CaptchaValid) {
            return;
        }

        $answer = is_string($value) ? trim($value) : '';
        if ('' === $answer) {
            return;
        }

        try {
            if (!$this->captcha->verify($constraint->type, $answer)) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        } catch (CaptchaLockedException) {
            $this->context->buildViolation($constraint->lockedMessage)->addViolation();
        }
    }
}
