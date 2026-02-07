<?php

namespace App\Domain\Application\Exception;

use App\Domain\Application\Enum\ContentStatus;

final class InvalidContentTransitionException extends \DomainException
{
    public static function cannotPublish(ContentStatus $status): self
    {
        return new self(
            sprintf('Cannot publish content with status "%s".', $status->value)
        );
    }

    public static function cannotEdit(ContentStatus $status): self
    {
        return new self(
            sprintf('Cannot edit content with status "%s".', $status->value)
        );
    }

    public static function cannotArchive(ContentStatus $status): self
    {
        return new self(
            sprintf('Cannot archive content with status "%s".', $status->value)
        );
    }
}
