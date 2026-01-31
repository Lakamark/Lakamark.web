<?php

namespace App\Tests;

abstract class EventTestCase extends KernelTestCase
{
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
