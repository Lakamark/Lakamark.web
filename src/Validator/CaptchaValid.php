<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class CaptchaValid extends Constraint
{
    public function __construct(
        public readonly string $type = 'puzzle',
        public readonly string $message = 'Captcha invalide.',
        public readonly string $lockedMessage = 'The captcha is locked.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }
}
