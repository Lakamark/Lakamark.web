<?php

namespace App\Domain\Subscription\Gateway;

use App\Domain\Auth\Entity\User;

/**
 * We can use this class for testing purpose only!
 * To stimulate a user is a premium member.
 * Don't use in the production.
 *
 * When will implement the subscription,
 * we will change by definitive SubscriptionGateway class.
 *
 * ----Change it in the service.yml----
 * App\Domain\Subscription\Gateway\FakeSubscriptionGateway:  ~
 *
 * App\Domain\Subscription\Gateway\SubscriptionGatewayInterface:
 * alias: App\Domain\Subscription\Gateway\FakeSubscriptionGateway
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
