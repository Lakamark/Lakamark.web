<?php

namespace App\Validator;

use App\Foundation\Captcha\Contract\CaptchaVerifierInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CaptchaValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly CaptchaVerifierInterface $captcha,
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

        if (!$this->captcha->consumeVerified($constraint->type)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
