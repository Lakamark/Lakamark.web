<?php

namespace App\Foundation\Adapter;

use App\Domain\Application\Contract\SluggerInterface;
use Symfony\Component\String\Slugger\SluggerInterface as SymfonySlugger;

final readonly class SymfonySluggerAdapter implements SluggerInterface
{
    public function __construct(
        private SymfonySlugger $slugger,
    ) {
    }

    public function slug(string $text): string
    {
        return strtolower($this->slugger->slug($text)->toString());
    }
}
