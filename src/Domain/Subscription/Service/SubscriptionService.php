<?php

namespace App\Domain\Subscription\Service;

use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Enum\SubscriptionProvider;
use App\Domain\Subscription\Enum\SubscriptionStatus;
use App\Domain\Subscription\Repository\SubscriptionRepository;

final readonly class SubscriptionService
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
    ) {
    }

    /**
     * Check if the current user has an activate subscription.
     */
    public function hasActiveSubscription(
        int $userId,
        ?\DateTimeImmutable $now = null,
    ): bool {
        $now ??= new \DateTimeImmutable();

        return $this->subscriptionRepository->hasActiveSubscription($userId, $now);
    }

    /**
     * Manually activate or cancel a subscription.
     */
    public function activateManual(int $userId, \DateTimeImmutable $periodEnd): void
    {
        $now = new \DateTimeImmutable();

        $subscription = $this->subscriptionRepository->findOneByUserId($userId);

        if (!$subscription) {
            $subscription = (new Subscription())
                ->setUserId($userId)
                ->setStatus(SubscriptionStatus::ACTIVE)
                ->setProvider(SubscriptionProvider::MANUAL)
                ->setStartedAt($now)
                ->setCurrentPeriodEnd($periodEnd)
                ->setUpdatedAt($now);

            $this->subscriptionRepository->save($subscription);

            return;
        }

        // Already active: extend only if later (idempotent)
        if (SubscriptionStatus::ACTIVE === $subscription->getStatus()) {
            if ($periodEnd <= $subscription->getCurrentPeriodEnd()) {
                return; // Do nothing
            }

            // Update the subscription.
            $subscription
                ->setCurrentPeriodEnd($periodEnd)
                ->setUpdatedAt($now);

            $this->subscriptionRepository->save($subscription);

            return;
        }

        // Cancel/Expired -> reactive
        $subscription
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setProvider(SubscriptionProvider::MANUAL)
            ->setCurrentPeriodEnd($periodEnd)
            ->setCanceledAt(null)
            ->setUpdatedAt($now);
    }
}
