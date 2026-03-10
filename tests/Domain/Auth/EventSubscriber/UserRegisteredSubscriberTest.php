<?php

namespace App\Tests\Domain\Auth\EventSubscriber;

use App\Domain\Auth\Event\UserRegisteredEvent;
use App\Domain\Auth\Event\UserResentConfirmationEvent;
use App\Domain\Auth\Subscriber\AuthSubscriber;
use App\Tests\EventTestCase;

class UserRegisteredSubscriberTest extends EventTestCase
{
    public function testSubscriberListensToUserRegisteredEvent(): void
    {
        $this->expectSubscribedEventTo(
            AuthSubscriber::class,
            UserRegisteredEvent::class,
        );
    }

    public function testSubscriberListensToUserResentConfirmationEvent(): void
    {
        $this->expectSubscribedEventTo(
            AuthSubscriber::class,
            UserResentConfirmationEvent::class,
        );
    }
}
