<?php

namespace App\Http\Security\Voter;

use App\Domain\Application\Entity\Content;
use App\Domain\Application\Security\ContentAccessPolicy;
use App\Domain\Auth\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ContentVoter extends Voter
{
    public const string VIEW = 'CONTENT_VIEW';
    public const string EDIT = 'CONTENT_EDIT';
    public const string DELETE = 'CONTENT_DELETE';

    public function __construct(
        private readonly Security $security,
        private readonly ContentAccessPolicy $policy,
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

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var Content $content */
        $content = $subject;

        // Premium subscription later.
        // $hasPremium = $this->subscriptionService->hasActiveSubscription($user);

        return match ($attribute) {
            self::VIEW => $this->policy->canView($user, $content),
            self::EDIT => $this->policy->canEdit($user, $content),
            self::DELETE => $this->policy->canDelete($user, $content),
            default => false,
        };
    }
}
