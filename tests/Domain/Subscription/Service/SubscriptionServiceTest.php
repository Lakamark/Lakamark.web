<?php

namespace App\Tests\Domain\Subscription\Service;

use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Enum\SubscriptionProvider;
use App\Domain\Subscription\Enum\SubscriptionStatus;
use App\Domain\Subscription\Repository\SubscriptionRepository;
use App\Domain\Subscription\Service\SubscriptionService;
use PHPUnit\Framework\TestCase;

class SubscriptionServiceTest extends TestCase
{
    public function testHasActiveSubscription(): void
    {
        $repository = $this->createMock(SubscriptionRepository::class);
        $repository->expects($this->once())
            ->method('hasActiveSubscription')
            ->with(12, $this->isInstanceOf(\DateTimeImmutable::class))
            ->willReturn(true);

        $service = new SubscriptionService($repository);

        $this->assertTrue($service->hasActiveSubscription(12));
    }

    public function testActivateManualCreatesSubscriptionIfNone(): void
    {
        $repo = $this->createMock(SubscriptionRepository::class);
        $repo->method('findOneByUserId')
            ->with(12)
            ->willReturn(null);

        $repo->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Subscription::class));

        $service = new SubscriptionService($repo);

        $service->activateManual(12, new \DateTimeImmutable('+10 days'));
    }

    public function testActivateManualExtendsIfNewPeriodIsLater(): void
    {
        $oldEnd = new \DateTimeImmutable('2026-02-25 12:00:00');
        $newEnd = new \DateTimeImmutable('2026-03-10 12:00:00');

        $existing = (new Subscription())
            ->setUserId(12)
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setCurrentPeriodEnd($oldEnd);

        $repo = $this->createMock(SubscriptionRepository::class);
        $repo->method('findOneByUserId')->willReturn($existing);
        $repo->expects($this->once())->method('save');

        $service = new SubscriptionService($repo);

        $service->activateManual(12, $newEnd);

        $this->assertSame($newEnd->getTimestamp(), $existing->getCurrentPeriodEnd()->getTimestamp());
    }

    public function testCancelManualDoesNothingWhenNoSubscription(): void
    {
        $repo = $this->createMock(SubscriptionRepository::class);
        $repo->method('findOneByUserId')
            ->with(17)
            ->willReturn(null);

        $repo->expects($this->never())->method('save');

        $service = new SubscriptionService($repo);

        $service->cancelManual(17, new \DateTimeImmutable('2026-02-25 12:00:00'));
    }

    public function testCancelManualDoesNothingWhenAlreadyCanceled(): void
    {
        $sub = (new Subscription())
            ->setUserId(12)
            ->setStatus(SubscriptionStatus::CANCELED)
            ->setProvider(SubscriptionProvider::MANUAL)
            ->setStartedAt(new \DateTimeImmutable('2026-02-01 12:00:00'))
            ->setCurrentPeriodEnd(new \DateTimeImmutable('2026-03-01 12:00:00'))
            ->setCanceledAt(new \DateTimeImmutable('2026-02-10 12:00:00'))
            ->setUpdatedAt(new \DateTimeImmutable('2026-02-10 12:00:00'));

        $repo = $this->createMock(SubscriptionRepository::class);
        $repo->method('findOneByUserId')
            ->with(12)
            ->willReturn($sub);

        $repo->expects($this->never())->method('save');

        $service = new SubscriptionService($repo);

        $service->cancelManual(12, new \DateTimeImmutable('2026-02-25 12:00:00'));
    }

    public function testCancelManualCancelsActiveSubscription(): void
    {
        $sub = (new Subscription())
            ->setUserId(12)
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setProvider(SubscriptionProvider::MANUAL)
            ->setStartedAt(new \DateTimeImmutable('2026-02-01 12:00:00'))
            ->setCurrentPeriodEnd(new \DateTimeImmutable('2026-03-01 12:00:00'))
            ->setUpdatedAt(new \DateTimeImmutable('2026-02-01 12:00:00'));

        $endedAt = new \DateTimeImmutable('2026-02-25 12:00:00');

        $repo = $this->createMock(SubscriptionRepository::class);
        $repo->method('findOneByUserId')->with(12)->willReturn($sub);

        $repo->expects($this->once())->method('save')->with($sub);

        $service = new SubscriptionService($repo);

        $service->cancelManual(12, $endedAt);

        // Be carful!
        // we should compare timestamp to avoid some issue with the DateTime object.
        $this->assertSame(SubscriptionStatus::CANCELED, $sub->getStatus());
        $this->assertSame($endedAt->getTimestamp(), $sub->getCanceledAt()?->getTimestamp());
    }

    public function testActivateFromPatreonSetsProviderAndRefWhenCreating(): void
    {
        $repo = $this->createMock(SubscriptionRepository::class);
        $repo->method('findOneByUserId')->willReturn(null);

        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Subscription $s) {
                return SubscriptionProvider::PATREON === $s->getProvider()
                    && 'pat_123' === $s->getProviderRef();
            }));

        $service = new SubscriptionService($repo);

        $service->activateFromPatreon(12, 'pat_123', new \DateTimeImmutable('+10 days'));
    }
}
