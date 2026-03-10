<?php

declare(strict_types=1);

namespace App\Domain\Auth\Enum;

enum OAuthProvider: string
{
    case LOCAL = 'local';
    case GOOGLE = 'google';
    case DISCORD = 'discord';
    case GITHUB = 'github';
    case PATREON = 'patreon';
}
