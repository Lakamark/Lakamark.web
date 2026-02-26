<?php

namespace App\Tests\Domain\Subscription;

use App\Domain\Subscription\Entity\Subscription;
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
}
