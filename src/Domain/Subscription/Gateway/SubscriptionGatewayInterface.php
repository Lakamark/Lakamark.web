<?php

namespace App\Domain\Subscription\Gateway;

use App\Domain\Auth\Entity\User;

interface SubscriptionGatewayInterface
{
    /**
     * Check if the current user has activeSubscription.
     */
    public function hasActiveSubscription(User $user): bool;
}
