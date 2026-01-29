<?php

declare(strict_types=1);

namespace App\Domain\Application\Enum;

enum ContentStatusEnum: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
