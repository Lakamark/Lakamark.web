<?php

namespace App\Foundation\Captcha;

use App\Foundation\Captcha\Contract\CaptchaChallengeInterface;
use App\Foundation\Captcha\Contract\CaptchaGeneratorInterface;
use App\Foundation\Captcha\Contract\CaptchaRegistryInterface;
use App\Foundation\Captcha\Exception\CaptchaInvalidArgumentException;

final readonly class CaptchaRegistry implements CaptchaRegistryInterface
{
    /**
     * @param array<string, CaptchaChallengeInterface> $challenges
     * @param array<string, CaptchaGeneratorInterface> $generators
     */
    public function __construct(
        private iterable $challenges,
        private iterable $generators,
    ) {
    }

    public function challenge(string $type): CaptchaChallengeInterface
    {
        if (!isset($this->challenges[$type])) {
            throw new CaptchaInvalidArgumentException("Unknown captcha type: $type");
        }

        return $this->challenges[$type];
    }

    public function generator(string $type): CaptchaGeneratorInterface
    {
        if (!isset($this->generators[$type])) {
            throw new CaptchaInvalidArgumentException("Unknown captcha generator: $type");
        }

        return $this->generators[$type];
    }
}
