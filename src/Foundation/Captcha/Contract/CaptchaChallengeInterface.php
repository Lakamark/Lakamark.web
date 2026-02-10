<?php

namespace App\Foundation\Captcha\Contract;

interface CaptchaChallengeInterface
{
    public function generateKey(): string;

    public function verify(string $key, string $answer): bool;

    public function getSolution(string $key): mixed;
}
