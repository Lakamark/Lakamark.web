<?php

namespace App\Http\Twig;

use App\Domain\Auth\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UserContextTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('email_unverified', $this->isEmailUnverified(...)),
        ];
    }

    public function isEmailUnverified(): bool
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return !$user->isEmailConfirmed();
    }
}
