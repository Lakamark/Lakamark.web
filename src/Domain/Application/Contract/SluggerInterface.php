<?php

namespace App\Domain\Application\Contract;

interface SluggerInterface
{
    public function slug(string $text): string;
}
