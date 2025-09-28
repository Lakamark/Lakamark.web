<?php

namespace App\Domain\Captcha;

use Symfony\Component\HttpFoundation\Response;

interface ChallengeGeneratorInterface
{
    public function generate(string $challengeKey): Response;
}
