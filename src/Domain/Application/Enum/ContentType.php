<?php

declare(strict_types=1);

namespace App\Domain\Application\Enum;

enum ContentType: string
{
    case POST = 'post';
    case PROJECT = 'project';
}
