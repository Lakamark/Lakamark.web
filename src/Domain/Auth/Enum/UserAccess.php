<?php

declare(strict_types=1);

namespace App\Domain\Auth\Enum;

enum UserAccess: string
{
    case VERIFIED = 'verified';
    case NOT_BANNED = 'not_banned';
}
