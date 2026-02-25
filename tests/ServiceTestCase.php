<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @template S of object
 */
abstract class ServiceTestCase extends KernelTestCase
{
    /**
     * @var S
     */
    protected $service;

    /**
     * @var class-string<S>
     */
    protected string $serviceClass;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = self::getContainer()->get($this->serviceClass);
    }
}
