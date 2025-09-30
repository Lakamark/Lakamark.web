<?php

namespace App\Validator;

use App\Domain\Captcha\CaptchaChallengeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CaptchaValidator extends ConstraintValidator
{
    public function __construct(
        private readonly CaptchaChallengeInterface $captchaChallenge,
    ) {
    }

    /**
     * @param array{challenge: string, answer: string} $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->captchaChallenge->verify($value['challenge'], $value['answer'] ?? '')) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
