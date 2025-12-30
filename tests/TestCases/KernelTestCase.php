<?php

namespace App\Tests\TestCases;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class KernelTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        parent::setUp();
    }
}
