<?php

namespace App\Foundation\Security\Contract;

use Random\RandomException;

interface TokenGeneratorInterface
{
    /**
     * @throws RandomException
     */
    public function generate(?int $bytes = null): string;
}
