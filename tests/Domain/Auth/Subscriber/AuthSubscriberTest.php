<?php

namespace App\Tests\Domain\Auth\Subscriber;

use App\Domain\Auth\Event\BeforeUserRegisterEvent;
use App\Domain\Auth\Event\ConfirmationEmailRequestedEvent;
use App\Domain\Auth\Event\ConfirmationTokenIssuedEvent;
use App\Domain\Auth\Event\UserRegisteredEvent;
use App\Domain\Auth\Subscriber\AuthSubscriber;
use App\Tests\EventTestCase;

class AuthSubscriberTest extends EventTestCase
{
    public function testSubscriberListensToBeforeUserRegisterEvent(): void
    {
        $this->expectSubscribedEventTo(
            AuthSubscriber::class,
            BeforeUserRegisterEvent::class,
        );
    }

    public function testSubscriberListensToUserRegisteredEvent(): void
    {
        $this->expectSubscribedEventTo(
            AuthSubscriber::class,
            UserRegisteredEvent::class,
        );
    }

    public function testSubscriberListensToConfirmationTokenIssuedEvent(): void
    {
        $this->expectSubscribedEventTo(
            AuthSubscriber::class,
            ConfirmationTokenIssuedEvent::class,
        );
    }

    public function testSubscriberListensToConfirmationEmailRequestedEvent(): void
    {
        $this->expectSubscribedEventTo(
            AuthSubscriber::class,
            ConfirmationEmailRequestedEvent::class,
        );
    }
}
