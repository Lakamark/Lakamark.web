<?php

namespace App\Foundation\Captcha\Contract;

interface CaptchaVerifierInterface
{
    public function verify(?string $type, string $answer, ?string $challenge = null): bool;

    public function isVerified(?string $type): bool;

    public function consumeVerified(?string $type): bool;
}
