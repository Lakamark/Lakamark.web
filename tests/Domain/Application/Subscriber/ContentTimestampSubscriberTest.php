<?php

namespace App\Tests\Domain\Application\Subscriber;

use App\Domain\Application\Event\BeforeCreatedContentEvent;
use App\Domain\Application\Event\BeforeUpdatedContentEvent;
use App\Domain\Application\Exception\DoubleSetException;
use App\Domain\Application\Subscriber\ContentTimestampSubscriber;
use App\Tests\Domain\Application\Entity\ContentStub;
use App\Tests\Helper\FixedClock;
use PHPUnit\Framework\TestCase;

class ContentTimestampSubscriberTest extends TestCase
{
    public function testItSetsCreatedAtOnBeforeCreatedEvent(): void
    {
        $clock = new FixedClock(new \DateTimeImmutable());
        $now = $clock->now();

        $subscriber = new ContentTimestampSubscriber();
        $content = new ContentStub(title: 'My Title', slug: null);
        $event = new BeforeCreatedContentEvent($content, $now);

        $subscriber->onBeforeCreatedContent($event);

        $this->assertSame($clock->now(), $content->getCreatedAt());
        $this->assertNull($content->getUpdatedAt());
    }

    public function testItSetsUpdatedAtOnBeforeUpdatedEvent(): void
    {
        $clock = new FixedClock(new \DateTimeImmutable());
        $now = $clock->now();

        $subscriber = new ContentTimestampSubscriber();
        $content = new ContentStub(title: 'My Title', slug: null);
        $event = new BeforeUpdatedContentEvent($content, $now);

        $subscriber->onBeforeUpdatedContent($event);

        $this->assertSame($clock->now(), $content->getUpdatedAt());
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function testItSetsDoubleCreatedAtOnBeforeCreatedEvent(): void
    {
        $clock = new FixedClock(new \DateTimeImmutable());
        $now = $clock->now();
        $now2 = $now->modify('+1 second');

        $subscriber = new ContentTimestampSubscriber();
        $content = new ContentStub(title: 'My Title', slug: null);
        $event = new BeforeCreatedContentEvent($content, $now);
        $event2 = new BeforeCreatedContentEvent($content, $now2);

        $this->expectException(DoubleSetException::class);

        $subscriber->onBeforeCreatedContent($event);
        $subscriber->onBeforeCreatedContent($event2);
    }
}
