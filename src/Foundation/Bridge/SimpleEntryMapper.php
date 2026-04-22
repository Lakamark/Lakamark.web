<?php

namespace App\Foundation\Bridge;

use App\Foundation\Bridge\Contract\EntryMapperInterface;

/**
 * SimpleEntryMapper.
 *
 * Transforms a logical entry name (e.g. "app", "dashboard") into a valid
 * Vite manifest key (e.g. "app.ts", "dashboard.ts").
 *
 * This class acts as a convention layer between Twig (or any caller)
 * and the Vite manifest structure used in production.
 *
 * Responsibilities:
 * - Normalize entry names (trim, remove leading slash)
 * - Remove "assets/" prefix if present
 * - Ensure a valid file extension (.ts or .js)
 * - Default to ".ts" when no extension is provided
 *
 * Examples:
 * - "app"            → "app.ts"
 * - "dashboard"      → "dashboard.ts"
 * - "app.ts"         → "app.ts"
 * - "/assets/app.ts" → "app.ts"
 *
 * Notes:
 * - This mapper does not validate the existence of the entry in the manifest.
 * - It assumes a TypeScript-first setup and defaults to ".ts".
 * - It remains compatible with ".js" entries if explicitly provided.
 *
 * @implements EntryMapperInterface
 */
readonly class SimpleEntryMapper implements EntryMapperInterface
{
    public function __construct()
    {
    }

    /**
     * Maps a given entry name to a Vite manifest key.
     *
     * @param string $entry Logical entry name (e.g. "app", "dashboard", "app.ts")
     *
     * @return string Normalized entry key matching the manifest format
     *
     * @throws \InvalidArgumentException If the entry is empty
     */
    public function map(string $entry): string
    {
        $entry = trim($entry);

        if ('' === $entry) {
            throw new \InvalidArgumentException('Entry name cannot be empty.');
        }

        // Remove leading slash if present (e.g. "/app.ts")
        $entry = ltrim($entry, '/');

        // Normalize paths starting with "assets/"
        if (str_starts_with($entry, 'assets/')) {
            $entry = substr($entry, 7);
        }

        // Ensure the entry has a valid extension (.ts or .js)
        // Default to TypeScript (.ts) if missing
        if (!preg_match('/\.(ts|js)$/', $entry)) {
            $entry .= '.ts';
        }

        return $entry;
    }
}
