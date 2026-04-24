<?php

namespace App\Foundation\Bridge\Contract;

use App\Foundation\Bridge\Exception\AssetManifestInvalidException;
use App\Foundation\Bridge\Exception\AssetManifestNotFoundException;

/**
 * Contract for reading and validating a Vite manifest file.
 *
 * This interface defines a low-level service responsible for:
 * - Reading the manifest file from a configured location
 * - Decoding the JSON content
 * - Validating the minimal structure of the manifest
 *
 * It does NOT:
 * - Resolve specific entries (handled by resolvers)
 * - Apply any business logic
 * - Handle environment-specific behavior (dev/prod)
 *
 * Design intention:
 * -----------------
 * The ManifestReader is intentionally isolated to avoid "magic" behavior
 * and silent failures when working with asset resolution.
 *
 * By centralizing manifest reading and validation:
 * - We ensure a single source of truth for manifest data
 * - We fail fast when the manifest is missing or invalid
 * - We simplify testing by decoupling file reading from resolution logic
 *
 * Usage:
 * ------
 * This service is typically used by an AssetResolver implementation
 * to retrieve the raw manifest data before resolving a specific entry.
 *
 * Example:
 * --------
 * $manifest = $reader->read();
 *
 * @return array<string, mixed>
 *
 * @throws AssetManifestNotFoundException
 * @throws AssetManifestInvalidException
 */
interface ManifestReaderInterface
{
    /**
     * Reads and returns the decoded manifest content.
     *
     * @return array<string, mixed> The decoded manifest as an associative array
     */
    public function read(): array;
}