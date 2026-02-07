<?php

namespace App\Foundation\Provider;

use App\Domain\Application\Enum\ContentType;
use Faker\Generator;
use Faker\Provider\Base as BaseProvider;

class ContentTypeProvider extends BaseProvider
{
    public function __construct(
        Generator $generator,
    ) {
        parent::__construct($generator);
    }

    public function randomCategoryType(): ContentType
    {
        $cases = ContentType::cases();

        return $cases[array_rand($cases)];
    }
}
