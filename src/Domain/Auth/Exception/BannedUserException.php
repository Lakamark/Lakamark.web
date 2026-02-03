<?php

namespace App\Domain\Auth\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class BannedUserException extends CustomUserMessageAccountStatusException
{
    public function __construct()
    {
        parent::__construct('Banned user.');
    }
}
