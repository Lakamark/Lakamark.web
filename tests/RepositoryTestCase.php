<?php

namespace App\Tests;

/**
 * @template E
 */
class RepositoryTestCase extends KernelTestCase
{
    /**
     * @var E
     */
    protected mixed $repository;

    /**
     * @var class-string<E>
     */
    protected string $repositoryClass;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = self::getContainer()->get($this->repositoryClass);
    }
}
