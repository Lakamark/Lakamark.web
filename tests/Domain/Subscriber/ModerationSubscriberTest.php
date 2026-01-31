<?php

namespace App\Tests\Domain\Subscriber;

use App\Domain\Moderation\Event\BannedUserEvent;
use App\Domain\Moderation\Event\UnbannedUserEvent;
use App\Domain\Moderation\Subscriber\ModerationSubscriber;
use App\Tests\EventTestCase;

class ModerationSubscriberTest extends EventTestCase
{
    public function testModerationSubscriberListenEvents(): void
    {
        $this->expectSubscribedEventTo(
            ModerationSubscriber::class,
            BannedUserEvent::class
        );

        $this->expectSubscribedEventTo(
            ModerationSubscriber::class,
            UnbannedUserEvent::class
        );
    }
}
