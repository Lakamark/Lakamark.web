<?php

namespace App\Foundation\Bridge;

use App\Foundation\Bridge\Contract\EntryMapperInterface;

readonly class SimpleEntryMapper implements EntryMapperInterface
{
    public function __construct(
        private string $baseDir = 'assets',
    ) {
    }

    public function map(string $entry): string
    {
        $entry = trim($entry);

        if ('' === $entry) {
            throw new \InvalidArgumentException('Entry name cannot be empty.');
        }

        return sprintf('%s/%s.js', trim($this->baseDir, '/'), $entry);
    }
}
