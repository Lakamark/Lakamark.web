<?php

namespace App\Foundation\Bridge;

use App\Foundation\Bridge\Contract\ManifestReaderInterface;
use App\Foundation\Bridge\Exception\AssetManifestInvalidException;
use App\Foundation\Bridge\Exception\AssetManifestNotFoundException;

/*
 * Reads and validates the Vite manifest file.
 *
 * This class is a low-level infrastructure component responsible for:
 * - Loading the manifest file from the filesystem
 * - Decoding its JSON content
 * - Validating its minimal structure
 *
 * It acts as the single source of truth for raw manifest data.
 *
 * Design philosophy:
 * ------------------
 * This class is intentionally simple and strict:
 *
 * - It does NOT resolve entries (handled by resolvers)
 * - It does NOT contain business logic
 * - It does NOT perform environment-specific behavior (dev/prod)
 * - It does NOT apply fallbacks
 *
 * Its only responsibility is to return a valid manifest or fail explicitly.
 *
 * Failure behavior:
 * -----------------
 * This reader throws explicit exceptions when:
 *
 * - The manifest file does not exist
 * - The file cannot be read
 * - The JSON is invalid
 * - The structure is not a valid JSON object
 *
 * This ensures that errors are detected early and prevents silent failures
 * in the asset resolution pipeline.
 *
 * Testing:
 * --------
 * This class is designed to be easily testable:
 *
 * - No dependency on the Symfony container
 * - No external side effects beyond filesystem access
 * - Can be instantiated with a custom path
 *
 * Usage in the pipeline:
 * ----------------------
 * Twig Extension → AssetBridge → Resolver → ManifestReader
 *
 * The resolver interprets the manifest and resolves specific entries.
 *
 * Maintenance note:
 * -----------------
 * Do not add resolution logic or environment-specific behavior here.
 * This class must remain a pure reader.
 *
 * @see ManifestReaderInterface
 */

final readonly class ManifestReader implements ManifestReaderInterface
{
    public function __construct(
        private string $manifestPath,
    ) {
    }

    /**
     * Reads and returns the decoded manifest content.
     *
     * @return array<string, mixed>
     *
     * @throws AssetManifestNotFoundException If the manifest file does not exist
     * @throws AssetManifestInvalidException  If the file cannot be read, JSON is invalid,
     *                                        or structure is not a valid object
     */
    public function read(): array
    {
        if (!is_file($this->manifestPath)) {
            throw new AssetManifestNotFoundException($this->manifestPath);
        }

        $content = file_get_contents($this->manifestPath);

        if (false === $content) {
            throw new AssetManifestInvalidException(sprintf('Unable to read manifest file at path "%s".', $this->manifestPath));
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new AssetManifestInvalidException(sprintf('Invalid JSON in manifest file "%s".', $this->manifestPath), $exception);
        }

        if (!is_array($decoded) || array_is_list($decoded)) {
            throw new AssetManifestInvalidException(sprintf('Manifest root must be a JSON object in "%s".', $this->manifestPath));
        }

        return $decoded;
    }
}
