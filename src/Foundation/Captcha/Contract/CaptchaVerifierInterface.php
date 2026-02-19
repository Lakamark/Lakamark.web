<?php

namespace App\Foundation\Captcha\Contract;

interface CaptchaVerifierInterface
{
    public function verify(?string $type, string $answer): bool;
}
