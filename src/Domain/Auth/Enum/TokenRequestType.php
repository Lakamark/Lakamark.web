<?php

declare(strict_types=1);

namespace App\Domain\Auth\Enum;

enum TokenRequestType: string
{
    case REGISTER_CONFIRMATION = 'confirmation';
    case EMAIL_CONFIRMATION = 'email_confirmation';
    case PASSWORD_RESET = 'password_reset';

    public function ttl(): \DateInterval
    {
        return match ($this) {
            self::REGISTER_CONFIRMATION,
            self::EMAIL_CONFIRMATION => new \DateInterval('PT2H'),
            self::PASSWORD_RESET => new \DateInterval('PT30M'),
        };
    }

    public function isSingleActive(): bool
    {
        return match ($this) {
            self::REGISTER_CONFIRMATION,
            self::EMAIL_CONFIRMATION,
            self::PASSWORD_RESET => true,
        };
    }
}
