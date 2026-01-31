<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;

abstract class CommandTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    protected Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
        parent::setUp();
    }
}
