<?php

namespace App\Foundation\Provider;

use App\Domain\Application\Enum\AccessLevel;
use Faker\Generator;
use Faker\Provider\Base as BaseProvider;

class AccessLevelProvider extends BaseProvider
{
    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
    }

    /**
     * Find the enum key from a value.
     */
    public function accessLevel(string $value): AccessLevel
    {
        return AccessLevel::tryFrom($value)
            ?? throw new \InvalidArgumentException(sprintf('Invalid access level "%s"', $value));
    }
}
