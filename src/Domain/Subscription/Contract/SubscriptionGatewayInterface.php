<?php

namespace App\Domain\Subscription\Contract;

use App\Domain\Auth\Entity\User;

/**
 * Subscription gateway contract.
 */
interface SubscriptionGatewayInterface
{
    /**
     * Returns whether the given user currently has an active subscription.
     */
    public function hasActiveSubscription(User $user): bool;
}
