<?php

namespace App\Tests\Domain\Application\Subscriber;

use App\Domain\Application\Contract\SluggerInterface;
use App\Domain\Application\Event\BeforeCreatedContentEvent;
use App\Domain\Application\Event\BeforeUpdatedContentEvent;
use App\Domain\Application\Subscriber\ContentSluggerSubscriber;
use App\Tests\Domain\Application\Entity\ContentStub;
use App\Tests\Helper\FixedClock;
use PHPUnit\Framework\TestCase;

class ContentSluggerSubscriberTest extends TestCase
{
    public function testOnCreatedContentNormalizesCustomSlugWhenProvided(): void
    {
        $slugger = $this->createMock(SluggerInterface::class);
        $clock = new FixedClock(new \DateTimeImmutable());
        $slugger->expects($this->once())
            ->method('slug')
            ->with('My Title')
            ->willReturn('my-title');

        $subscriber = new ContentSluggerSubscriber($slugger);
        $content = new ContentStub(title: 'My Title', slug: null);
        $event = new BeforeCreatedContentEvent($content, $clock->now());

        $subscriber->onCreatedContent($event);

        $this->assertSame('my-title', $content->getSlug());
    }

    public function testCreatedContentDoesNotOverrideCustomSlug(): void
    {
        $slugger = $this->createMock(SluggerInterface::class);
        $slugger->expects($this->never())->method('slug');
        $clock = new FixedClock(new \DateTimeImmutable());

        $subscriber = new ContentSluggerSubscriber($slugger);

        $content = new ContentStub(title: 'My Title', slug: 'custom-slug');
        $event = new BeforeCreatedContentEvent($content, $clock->now());

        $subscriber->onCreatedContent($event);

        $this->assertSame('custom-slug', $content->getSlug());
    }

    public function testUpdatedContentBehavesSameAsCreated(): void
    {
        $slugger = $this->createMock(SluggerInterface::class);
        $clock = new FixedClock(new \DateTimeImmutable());
        $slugger->expects($this->once())
            ->method('slug')
            ->with('Updated Title')
            ->willReturn('updated-title');

        $subscriber = new ContentSluggerSubscriber($slugger);

        $content = new ContentStub(title: 'Updated Title', slug: null);
        $event = new BeforeUpdatedContentEvent($content, $clock->now());

        $subscriber->onUpdatedContent($event);

        $this->assertSame('updated-title', $content->getSlug());
    }
}
