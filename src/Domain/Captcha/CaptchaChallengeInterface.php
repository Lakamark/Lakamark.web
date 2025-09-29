<?php

namespace App\Domain\Captcha;

interface CaptchaChallengeInterface
{
    public function generateKey(): string;

    public function verify(string $key, string $answer): bool;

    public function getSolution(string $Key): mixed;
}
