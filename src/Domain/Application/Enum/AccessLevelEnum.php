<?php declare(strict_types=1);

namespace App\Domain\Application\Enum;

enum AccessLevelEnum: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case PREMIUM_MEMBER_ONLY = 'premium_member_only';
}
