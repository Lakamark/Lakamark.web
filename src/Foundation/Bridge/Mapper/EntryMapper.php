<?php

namespace App\Foundation\Bridge\Mapper;

use App\Foundation\Bridge\Contract\EntryMapperInterface;

class EntryMapper implements EntryMapperInterface
{
    public function map(string $entry): string
    {
        if (str_ends_with($entry, '.ts') || str_ends_with($entry, '.js')) {
            return $entry;
        }

        return $entry.'.ts';
    }
}
