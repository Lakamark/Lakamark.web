<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Enum;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case CANCELED = 'canceled';
    case EXPIRED = 'expired';
}
