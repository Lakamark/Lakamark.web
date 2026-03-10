<?php

declare(strict_types=1);

namespace App\Domain\Auth\Enum;

enum UserRole: string
{
    case USER = 'ROLE_USER';
    case USER_VERIFIED = 'ROLE_USER_VERIFIED';
    case ADMIN = 'ROLE_ADMIN';
    case EDITOR = 'ROLE_EDITOR';
    case MODERATOR = 'ROLE_MODERATOR';

    public function priority(): int
    {
        return match ($this) {
            self::ADMIN => 100,
            self::MODERATOR => 80,
            self::EDITOR => 60,
            self::USER_VERIFIED => 40,
            self::USER => 0,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::USER => 'User',
            self::USER_VERIFIED => 'Verified User',
            self::ADMIN => 'Administrator',
            self::EDITOR => 'Editor',
            self::MODERATOR => 'Moderator',
        };
    }

    public function isStaff(): bool
    {
        return $this->priority() >= 60;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public static function fromRole(string $role): self
    {
        return self::from($role);
    }

    /** @return list<self> */
    public static function staff(array $roles): array
    {
        return [
            self::ADMIN,
            self::EDITOR,
            self::MODERATOR,
        ];
    }
}
