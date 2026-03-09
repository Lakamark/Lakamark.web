<?php

namespace App\Tests;

use App\Tests\Helper\FixedClock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class DomainServiceTestCase extends KernelTestCase
{
    protected EntityManagerInterface $em;
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->container = static::getContainer();
        $this->em = $this->container->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        if (isset($this->em)) {
            $this->em->clear();
        }

        unset($this->em, $this->container);

        parent::tearDown();
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    protected function service(string $id): object
    {
        return $this->container->get($id);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $entityClass
     */
    protected function repository(string $entityClass): object
    {
        return $this->em->getRepository($entityClass);
    }

    protected function flushAndClear(): void
    {
        $this->em->flush();
        $this->em->clear();
    }

    protected function fixedClock(): FixedClock
    {
        $clock = $this->service(FixedClock::class);
        assert($clock instanceof FixedClock);

        return $clock;
    }

    protected function setFixedClock(\DateTimeImmutable $now): void
    {
        $this->fixedClock()->setNow($now);
    }
}
