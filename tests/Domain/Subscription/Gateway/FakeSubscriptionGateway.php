<?php

namespace App\Tests\Domain\Subscription\Gateway;

use App\Domain\Auth\Entity\User;
use App\Domain\Subscription\Contract\SubscriptionGatewayInterface;

/**
 * Fake subscription gateway used in tests to simulate
 * premium and non-premium subscription states.
 *
 * @internal
 */
final readonly class FakeSubscriptionGateway implements SubscriptionGatewayInterface
{
    public function __construct(private bool $active)
    {
    }

    public function hasActiveSubscription(User $user): bool
    {
        return $this->active;
    }
}
