<?php

namespace App\Domain\Auth\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class LockedUserException extends CustomUserMessageAuthenticationException
{
    public function __construct(
        string $message = 'This account was looked.',
        array $messageData = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $messageData, $code, $previous);
    }
}
