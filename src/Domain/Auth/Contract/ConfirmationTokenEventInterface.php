<?php

namespace App\Domain\Auth\Contract;

use App\Domain\Auth\DTO\IssuedTokenRequestDTO;

interface ConfirmationTokenEventInterface
{
    public function getIssuedTokenRequestDto(): IssuedTokenRequestDTO;
}
