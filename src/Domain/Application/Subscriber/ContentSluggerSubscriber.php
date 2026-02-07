<?php

namespace App\Domain\Application\Subscriber;

use App\Domain\Application\Contract\SluggerInterface;
use App\Domain\Application\Entity\Content;
use App\Domain\Application\Event\BeforeCreatedContentEvent;
use App\Domain\Application\Event\BeforeUpdatedContentEvent;
use App\Domain\Application\Exception\ContentLogicException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ContentSluggerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeCreatedContentEvent::class => 'onCreatedContent',
            BeforeUpdatedContentEvent::class => 'onUpdatedContent',
        ];
    }

    public function onCreatedContent(BeforeCreatedContentEvent $event): void
    {
        $this->ensureSlug($event->getContent());
    }

    public function onUpdatedContent(BeforeUpdatedContentEvent $event): void
    {
        $this->ensureSlug($event->getContent());
    }

    /**
     * The user can define a custom slug
     * if the slug is null we will generate the slug from the title.
     */
    private function ensureSlug(Content $content): void
    {
        $slug = trim((string) $content->getSlug());

        // If the user defined a custom slug,
        // we won't generate a slug from the title.
        if ('' !== $slug) {
            return;
        }

        $title = trim($content->getTitle());
        if ('' === $title) {
            throw new ContentLogicException('The content title cannot be empty before slug generation.');
        }

        $content->setSlug($this->slugger->slug($title));
    }
}
