<?php

namespace App\Domain\Application\Security;

use App\Domain\Application\Entity\Content;
use App\Domain\Application\Enum\AccessLevel;
use App\Domain\Application\Enum\ContentStatus;
use App\Domain\Auth\Entity\User;

final class ContentAccessPolicy
{
    public function canEdit(User $user, Content $content): bool
    {
        return $this->isAuthor($user, $content);
    }

    public function canDelete(User $user, Content $content): bool
    {
        return $this->isAuthor($user, $content);
    }

    public function canView(User $user, Content $content, bool $hasPremium = false): bool
    {
        // The owner content can anytime to see his content.
        if ($this->isAuthor($user, $content)) {
            return true;
        }

        // Draft -> Visible only by the owner content
        if (ContentStatus::DRAFT === $content->getStatus()) {
            return false;
        }

        // Archived -> visible only by the owner
        // Admin can get access if are not author of this content
        if (ContentStatus::ARCHIVED === $content->getStatus()) {
            return false;
        }

        return match ($content->getAccessLevel()) {
            AccessLevel::PUBLIC => true,

            AccessLevel::PRIVATE => false,

            // TODO: When SubscriptionService is implemented, allow PREMIUM_MEMBER_ONLY for active subscribers.
            AccessLevel::PREMIUM_MEMBER_ONLY => $hasPremium,
        };
    }

    private function isAuthor(User $user, Content $content): bool
    {
        $authorId = $content->getAuthor()->getId();
        $userId = $user->getId();

        return null !== $authorId && null !== $userId && $authorId === $userId;
    }
}
