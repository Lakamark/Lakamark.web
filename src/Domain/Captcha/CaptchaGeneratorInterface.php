<?php

namespace App\Domain\Captcha;

use Symfony\Component\HttpFoundation\Response;

interface CaptchaGeneratorInterface
{
    public function generate(string $key): Response;
}