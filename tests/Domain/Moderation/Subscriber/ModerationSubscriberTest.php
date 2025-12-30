<?php

namespace App\Tests\Domain\Moderation\Subscriber;

use App\Domain\Moderation\Event\BanUserEvent;
use App\Domain\Moderation\Event\UnbanUserEvent;
use App\Domain\Moderation\Subscriber\ModerationSubscriber;
use App\Tests\TestCases\EventTestCase;

class ModerationSubscriberTest extends EventTestCase
{
    public function testSubscribedToEvents(): void
    {
        $this->expectSubscribedEventTo(
            ModerationSubscriber::class,
            BanUserEvent::class
        );
        $this->expectSubscribedEventTo(
            ModerationSubscriber::class,
            UnbanUserEvent::class
        );
    }
}
