<?php

namespace App\Domain\Application\Exception;

class DoubleSetException extends \RuntimeException
{
    public static function for(string $field): self
    {
        return new (sprintf(
            '%s is immutable and cannot be set twice.',
            $field
        ));
    }
}
