<?php

namespace App\Foundation\Bridge\Contract;

interface UserAgentDeciderInterface
{
    public function shouldLoadCustomElementsPolyfill(string $userAgent): bool;
}
