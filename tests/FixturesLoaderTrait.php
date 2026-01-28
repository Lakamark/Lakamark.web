<?php

namespace App\Tests;

use App\Helper\PathConvertibleHelper;
use Fidry\AliceDataFixtures\LoaderInterface;

trait FixturesLoaderTrait
{
    /*
     * To load some fixtures in the testing database and to add to the entityManager.
     *
     * @param array<string> $fixtures
     *
     * @return array<string,object>
     */
    public function loadFixtures(array $fixtures): array
    {
        $fixturesPath = $this->getFixturesPath();
        $files = array_map(fn (string $fixture) => PathConvertibleHelper::join($fixturesPath, $fixture.'.yml'), $fixtures);

        /** @var LoaderInterface $loader */
        $loader = $this->getContainer()->get('fidry_alice_data_fixtures.loader.doctrine');

        return $loader->load($files);
    }

    private function getFixturesPath(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR;
    }
}
