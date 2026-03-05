<?php

namespace App\Http\Security\Voter;

use App\Domain\Application\Entity\Content;
use App\Domain\Application\Security\ContentAccessPolicy;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\UserRole;
use App\Domain\Auth\Service\UserRoleManagerService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ContentVoter extends Voter
{
    public const string VIEW = 'CONTENT_VIEW';
    public const string EDIT = 'CONTENT_EDIT';
    public const string DELETE = 'CONTENT_DELETE';

    public function __construct(
        private readonly ContentAccessPolicy $policy,
        private readonly UserRoleManagerService $roleManagerService,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Content
            && in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Content $content */
        $content = $subject;

        // Admin Bypass
        if ($this->roleManagerService->has($user, UserRole::ADMIN)) {
            return true;
        }

        // Premium subscription later.
        // $hasPremium = $this->subscriptionService->hasActiveSubscription($user);

        return match ($attribute) {
            self::VIEW => $this->policy->canView($user, $content),
            self::EDIT => $this->policy->canEdit($user, $content)
                || $this->roleManagerService->has($user, UserRole::EDITOR),
            self::DELETE => $this->policy->canDelete($user, $content)
                || $this->roleManagerService->has($user, UserRole::MODERATOR),
            default => false,
        };
    }
}
