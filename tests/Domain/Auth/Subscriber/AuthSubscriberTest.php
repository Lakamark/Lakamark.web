<?php

namespace App\Tests\Domain\Auth\Subscriber;

use App\Domain\Auth\Event\BeforeUserRegisterEvent;
use App\Domain\Auth\Event\UserRegisteredEvent;
use App\Domain\Auth\Subscriber\AuthSubscriber;
use PHPUnit\Framework\TestCase;

class AuthSubscriberTest extends TestCase
{
    public function testItSubscribesToBeforeUserRegisterEvent(): void
    {
        $events = AuthSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(BeforeUserRegisterEvent::class, $events);
        $this->assertSame('onBeforeUserRegister', $events[BeforeUserRegisterEvent::class]);
    }

    public function testItSubscribesToUserRegisteredEvent(): void
    {
        $events = AuthSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(UserRegisteredEvent::class, $events);
        $this->assertSame('onUserRegistered', $events[UserRegisteredEvent::class]);
    }
}
