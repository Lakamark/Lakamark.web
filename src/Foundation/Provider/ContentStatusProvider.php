<?php

namespace App\Foundation\Provider;

use App\Domain\Application\Enum\ContentStatus;
use Faker\Generator;
use Faker\Provider\Base as BaseProvider;

class ContentStatusProvider extends BaseProvider
{
    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
    }

    /**
     * Find the enum key from a value.
     */
    public function enumStatus(string $value): ContentStatus
    {
        return ContentStatus::from($value);
    }

    public function randomStatus(): ContentStatus
    {
        $cases = ContentStatus::cases();

        return $cases[array_rand($cases)];
    }
}
