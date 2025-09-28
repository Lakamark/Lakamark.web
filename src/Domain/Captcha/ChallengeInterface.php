<?php

namespace App\Domain\Captcha;

interface ChallengeInterface
{
    public function generateKey(): string;

    public function verify(string $key, string $answer): bool;

    public function getSolution(string $challengeKey): mixed;
}
