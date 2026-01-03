<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Enum;

enum SubscriptionStatusEnum: string
{
    case NONE = 'none';
    case ACTIVE = 'active';
    case CANCELED = 'canceled';
    case EXPIRED = 'expired';
}
