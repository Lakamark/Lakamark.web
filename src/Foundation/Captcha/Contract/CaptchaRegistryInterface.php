<?php

namespace App\Foundation\Captcha\Contract;

interface CaptchaRegistryInterface
{
    public function challenge(string $type): CaptchaChallengeInterface;

    public function generator(string $type): CaptchaGeneratorInterface;
}
