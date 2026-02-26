<?php

namespace App\Tests\Domain\Subscription\Subscriber;

use App\Domain\Subscription\Event\ActivateSubscriptionEvent;
use App\Domain\Subscription\Event\CanceledSubscriptionEvent;
use App\Domain\Subscription\Event\Patreon\ActivatePatronSubscriptionEvent;
use App\Domain\Subscription\Event\Patreon\CancelPatronSubscriptionEvent;
use App\Domain\Subscription\Service\SubscriptionService;
use App\Domain\Subscription\Subscriber\SubscriptionSubscriber;
use PHPUnit\Framework\TestCase;

class SubscriptionSubscriberTest extends TestCase
{
    public function testOnManualActivateCallsService(): void
    {
        $service = $this->createMock(SubscriptionService::class);
        $service->expects($this->once())
            ->method('activateManual')
            ->with(12, $this->isInstanceOf(\DateTimeImmutable::class));

        $subscriber = new SubscriptionSubscriber($service);

        $subscriber->onManualActivated(new ActivateSubscriptionEvent(
            12,
            new \DateTimeImmutable('+10 days'))
        );
    }

    public function testOnManualCancelCallsService(): void
    {
        $service = $this->createMock(SubscriptionService::class);
        $service->expects($this->once())
            ->method('cancelManual')
            ->with(12, $this->isInstanceOf(\DateTimeImmutable::class));

        $subscriber = new SubscriptionSubscriber($service);
        $subscriber->onManualCanceled(new CanceledSubscriptionEvent(
            12,
            new \DateTimeImmutable('+10 days'))
        );
    }

    public function testOnPatreonActivateCallsService(): void
    {
        $patronId = '123455';
        $service = $this->createMock(SubscriptionService::class);
        $service->expects($this->once())
            ->method('activateFromPatreon')
            ->with(
                12,
                $patronId,
                $this->isInstanceOf(\DateTimeImmutable::class)
            );

        $subscriber = new SubscriptionSubscriber($service);
        $subscriber->onPatreonActivated(new ActivatePatronSubscriptionEvent(
            12,
            $patronId,
            new \DateTimeImmutable('+10 days')
        ));
    }

    public function testOnPatreonCancelCallsService(): void
    {
        $patronId = '123455';
        $service = $this->createMock(SubscriptionService::class);
        $service->expects($this->once())
            ->method('cancelFromPatreon')
            ->with(
                12,
                $patronId,
                $this->isInstanceOf(\DateTimeImmutable::class)
            );

        $subscriber = new SubscriptionSubscriber($service);
        $subscriber->onPatreonCanceled(new CancelPatronSubscriptionEvent(
            12,
            $patronId,
            new \DateTimeImmutable('+10 days')
        ));
    }
}
