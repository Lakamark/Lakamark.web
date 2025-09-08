<?php

namespace App\Foundation\Security;

use Random\RandomException;

class TokenGeneratorService
{
    public function generateToken(int $sizeToken = 50): string
    {
        $sizeToken = max(2, min(PHP_INT_MAX, $sizeToken));

        /** @var int<1, max> $halfLength */
        $halfLength = (int) ceil($sizeToken / 2);

        return substr(bin2hex(random_bytes($halfLength)), 0, $sizeToken);
    }
}
