<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Enum;

enum SubscriptionProvider: string
{
    case PATREON = 'patreon';
    case STRIPE = 'stripe';
    case PAYPAL = 'paypal';
    case MANUAL = 'manual';
}
