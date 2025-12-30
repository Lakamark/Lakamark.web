<?php

namespace App\Domain\Auth\Exception;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;

final class BannedUserException extends BadCredentialsException
{
}
