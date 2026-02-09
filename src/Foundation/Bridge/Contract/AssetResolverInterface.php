<?php

namespace App\Foundation\Bridge\Contract;

interface AssetResolverInterface
{
    /** @return non-empty-string|null */
    public function resolveJs(string $entry): ?string;

    /** @return non-empty-string|null */
    public function resolveCss(string $entry): ?string;

    /** @return list<non-empty-string> */
    public function resolveImports(string $entry): array;
}
