<?php

namespace App\Domain\Auth\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class TooManyAttemptsException extends CustomUserMessageAuthenticationException
{
    public function __construct(
        string $message = 'The account has been locked. You exceeded the maximum number of failed login attempts.',
        array $messageData = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $messageData, $code, $previous);
    }
}
