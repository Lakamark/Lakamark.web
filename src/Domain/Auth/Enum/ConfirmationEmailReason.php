<?php

declare(strict_types=1);

namespace App\Domain\Auth\Enum;

enum ConfirmationEmailReason: string
{
    case REGISTER = 'register';
    case RESEND = 'resend';
}
