<?php

namespace App\Domain\Application\Security;

use App\Domain\Application\Contract\ReadableContentInterface;
use App\Domain\Application\Enum\AccessLevelEnum;
use App\Domain\Application\Enum\ContentStatusEnum;
use App\Domain\Auth\Entity\User;
use App\Domain\Subscription\Contract\SubscriptionGatewayInterface;

final readonly class ContentAccessPolicy
{
    public function __construct(
        private SubscriptionGatewayInterface $subscriptionGateway,
    ) {
    }

    public function canRead(ReadableContentInterface $content, ?User $viewer): bool
    {
        // If the content is unpublished, only the content owner can see it.
        if (ContentStatusEnum::PUBLISHED !== $content->getStatus()) {
            return $viewer === $content->getAuthor();
        }

        return match ($content->getAccessLevel()) {
            AccessLevelEnum::PUBLIC => true,
            AccessLevelEnum::PREMIUM_MEMBER_ONLY => null !== $viewer && $this->subscriptionGateway->hasActiveSubscription($viewer),
            AccessLevelEnum::PRIVATE => $viewer === $content->getAuthor(),
        };
    }
}
