<?php

namespace App\Http\Form\Model;

final class CaptchaValueDTO
{
    public function __construct(
        public ?string $type = null,
        public ?string $answer = null,
    ) {
    }
}
