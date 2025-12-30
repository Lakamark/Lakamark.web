<?php

namespace App\Tests\TestCases;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class EventTestCase extends KernelTestCase
{
    protected function dispatch(
        EventSubscriberInterface $subscriber,
        object $event,
    ): void {
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($subscriber);
        $dispatcher->dispatch($event);
    }

    /**
     * To test a subscriber class listen the right event.
     */
    protected function expectSubscribedEventTo(string $subscriberClass, string $expectedEventName): void
    {
        self::bootKernel();

        $subscriber = self::getContainer()->get($subscriberClass);
        $events = $subscriber::getSubscribedEvents();

        $this->assertArrayHasKey($expectedEventName, $events);
    }
}
