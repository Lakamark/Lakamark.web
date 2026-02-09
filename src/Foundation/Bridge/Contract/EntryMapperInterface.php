<?php

namespace App\Foundation\Bridge\Contract;

interface EntryMapperInterface
{
    /** @return non-empty-string */
    public function map(string $entry): string;
}
