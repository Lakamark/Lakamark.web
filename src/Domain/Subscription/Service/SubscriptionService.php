<?php

namespace App\Domain\Subscription\Service;

use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Enum\SubscriptionProvider;
use App\Domain\Subscription\Enum\SubscriptionStatus;
use App\Domain\Subscription\Repository\SubscriptionRepository;

readonly class SubscriptionService
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
     * Manually activate subscription.
     */
    public function activateManual(int $userId, \DateTimeImmutable $periodEnd): void
    {
        $this->activate($userId, SubscriptionProvider::MANUAL, null, $periodEnd);
    }

    /**
     * The user is premium Patreon membership.
     */
    public function activateFromPatreon(
        int $userId,
        string $patreonId,
        \DateTimeImmutable $periodEnd,
    ): void {
        $this->activate(
            $userId,
            SubscriptionProvider::PATREON,
            $patreonId,
            $periodEnd
        );
    }

    /**
     * Manually cancel subscription.
     */
    public function cancelManual(int $userId, \DateTimeImmutable $endedAt): void
    {
        $this->cancel($userId, SubscriptionProvider::MANUAL, null, $endedAt);
    }

    /**
     * If the user has subscription on the Lake Mark Patreon.
     * He is also a premium member on Lakamark.com.
     */

    /**
     * If the user decided to cancel the Patron membership.
     * He lost the premium advantage on lakamark.com.
     */
    public function cancelFromPatreon(
        int $userId,
        string $patreonId,
        \DateTimeImmutable $endedAt,
    ): void {
        $this->cancel($userId, SubscriptionProvider::PATREON, $patreonId, $endedAt);
    }

    /**
     * Activate or update a subscription.
     */
    private function activate(
        int $userId,
        SubscriptionProvider $provider,
        ?string $providerRef,
        \DateTimeImmutable $periodEnd,
    ): void {
        $now = new \DateTimeImmutable();
        $subscription = $this->subscriptionRepository->findOneByUserId($userId);

        if (!$subscription) {
            $subscription = (new Subscription())
                ->setUserId($userId)
                ->setStatus(SubscriptionStatus::ACTIVE)
                ->setProvider($provider)
                ->setProviderRef($providerRef)
                ->setStartedAt($now)
                ->setCurrentPeriodEnd($periodEnd)
                ->setUpdatedAt($now);

            $this->subscriptionRepository->save($subscription);

            return;
        }

        // Already active: extend only if later (idempotent)
        if (SubscriptionStatus::ACTIVE === $subscription->getStatus()) {
            if ($periodEnd <= $subscription->getCurrentPeriodEnd()) {
                return; // do nothing
            }

            // Extend the subscription.
            $subscription->setCurrentPeriodEnd($periodEnd)
                ->setProvider($provider)
                ->setProviderRef($providerRef)
                ->setUpdatedAt($now);

            $this->subscriptionRepository->save($subscription);

            return;
        }

        // Cancel/Expired -> reactive
        $subscription
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setProvider($provider)
            ->setProviderRef($providerRef)
            ->setCurrentPeriodEnd($periodEnd)
            ->setCanceledAt(null)
            ->setUpdatedAt($now);

        $this->subscriptionRepository->save($subscription);
    }

    private function cancel(
        int $userId,
        SubscriptionProvider $provider,
        ?string $providerRef,
        \DateTimeImmutable $endedAt,
    ): void {
        $subscription = $this->subscriptionRepository->findOneByUserId($userId);

        if (!$subscription) {
            return;
        }

        if (SubscriptionStatus::CANCELED === $subscription->getStatus()) {
            return;
        }

        if ($subscription->getProvider() !== $provider) {
            return; // ignore if provider mismatch
        }

        // clamp
        if ($endedAt > $subscription->getCurrentPeriodEnd()) {
            $endedAt = $subscription->getCurrentPeriodEnd();
        }

        $now = new \DateTimeImmutable();

        // update subscription info
        $subscription
            ->setStatus(SubscriptionStatus::CANCELED)
            ->setProvider($provider)
            ->setProviderRef($providerRef)
            ->setCanceledAt($endedAt)
            ->setUpdatedAt($now);

        $this->subscriptionRepository->save($subscription);
    }
}
