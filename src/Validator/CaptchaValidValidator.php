<?php

namespace App\Validator;

use App\Foundation\Captcha\Contract\CaptchaVerifierInterface;
use App\Foundation\Captcha\Exception\CaptchaLockedException;
use Symfony\Component\Form\FormInterface;
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

        // read the hidden field (challenge)
        $challenge = $this->getSubmittedChallenge($this->context->getRoot());

        try {
            if (!$this->captcha->verify($constraint->type, $answer, $challenge)) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        } catch (CaptchaLockedException) {
            $this->context->buildViolation($constraint->lockedMessage)->addViolation();
        }
    }

    /**
     * Get the submitted challenge from the form.
     */
    private function getSubmittedChallenge(mixed $root): ?string
    {
        if (!$root instanceof FormInterface) {
            return null;
        }

        if (!$root->has('challenge')) {
            return null;
        }

        $data = $root->get('challenge')->getData();

        return is_string($data) ? $data : null;
    }
}
