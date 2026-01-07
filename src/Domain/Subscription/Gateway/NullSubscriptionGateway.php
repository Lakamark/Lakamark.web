<?php

namespace App\Domain\Subscription\Gateway;

use App\Domain\Auth\Entity\User;
use App\Domain\Subscription\Contract\SubscriptionGatewayInterface;

/**
 * Null subscription gateway used in production as a placeholder.
 *
 * This is a temporary implementation until the real subscription
 * system is ready.
 */
class NullSubscriptionGateway implements SubscriptionGatewayInterface
{
    public function hasActiveSubscription(User $user): bool
    {
        return false;
    }
}
