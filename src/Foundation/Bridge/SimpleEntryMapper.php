<?php

namespace App\Foundation\Bridge;

use App\Foundation\Bridge\Contract\EntryMapperInterface;

readonly class SimpleEntryMapper implements EntryMapperInterface
{
    public function __construct()
    {
    }

    public function map(string $entry): string
    {
        $entry = trim($entry);

        if ('' === $entry) {
            throw new \InvalidArgumentException('Entry name cannot be empty.');
        }

        // Accept: "app" -> "app.js"
        if (!str_contains($entry, '.')) {
            $entry .= '.js';
        }

        // If you define "assets/app.js" or "/assets/app.js",
        // We remove the prefix.
        $entry = ltrim($entry, '/');
        if (str_starts_with($entry, 'assets/')) {
            $entry = substr($entry, 7);
        }

        return $entry;
    }
}
