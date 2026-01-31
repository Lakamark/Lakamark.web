<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Enum;

enum BanReason: string
{
    case SPAM = 'spam';
    case BOT = 'bot';
    case HARASSMENT = 'harassment';
    case TERMS_VIOLATION = 'terms_violation';
    case FRAUD = 'fraud';
    case ABUSE = 'abuse';
    case OTHER = 'other';
}
