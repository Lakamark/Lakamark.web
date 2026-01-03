<?php

namespace App\Domain\Subscription\Gateway;

use App\Domain\Auth\Entity\User;

/**
 * When will implement the subscription,
 *  we will change by definitive SubscriptionGateway class later.
 */
class NullSubscriptionGateway implements SubscriptionGatewayInterface
{
    public function hasActiveSubscription(User $user): bool
    {
        return false;
    }
}
