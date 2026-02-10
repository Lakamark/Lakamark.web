<?php

namespace App\Foundation\Captcha\Contract;

use Symfony\Component\HttpFoundation\Response;

interface CaptchaGeneratorInterface
{
    public function generate(string $key): Response;
}
