<?php

namespace App\Foundation\Faker\Provider;

use Faker\Generator;
use Faker\Provider\Base as BaseProvider;

final class BadgeActionNameProvider extends BaseProvider
{
    public const ACTIONS_NAMES = [
        'home',
        'comment',
        'validate',
        'profile',
        'read',
        'like',
        'follow',
    ];

    public function __construct(
        Generator $generator,
    ) {
        parent::__construct($generator);
    }

    public function randomActionName()
    {
        return self::randomElement(self::ACTIONS_NAMES);
    }
}
