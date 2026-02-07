<?php

namespace App\Domain\Application\Subscriber;

use App\Domain\Application\Event\BeforeCreatedContentEvent;
use App\Domain\Application\Event\BeforeUpdatedContentEvent;
use App\Domain\Application\Exception\DoubleSetException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ContentTimestampSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeCreatedContentEvent::class => 'onBeforeCreatedContent',
            BeforeUpdatedContentEvent::class => 'onBeforeUpdatedContent',
        ];
    }

    /**
     * @throws DoubleSetException
     */
    public function onBeforeCreatedContent(BeforeCreatedContentEvent $event): void
    {
        $content = $event->getContent();

        if (null !== $content->getCreatedAt()) {
            return;
        }

        $content->setCreatedAt($event->getNow());
    }

    public function onBeforeUpdatedContent(BeforeUpdatedContentEvent $event): void
    {
        $content = $event->getContent();
        $content->setUpdatedAt($event->getNow());
    }
}
