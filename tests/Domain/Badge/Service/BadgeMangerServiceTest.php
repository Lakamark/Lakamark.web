<?php

namespace App\Tests\Domain\Badge\Service;

use App\Domain\Badge\Service\BadgeMangerService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

class BadgeMangerServiceTest extends TestCase
{
    private BadgeMangerService $badgeMangerService;
    private MockObject|EntityManagerInterface $em;
    private MockObject|EventDispatcherInterface $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->badgeMangerService = new BadgeMangerService($this->em, $this->dispatcher);
    }

    public function testUnlockBadgeIfBadgeIsUnlocked(): void
    {
        $this->markTestIncomplete('TODO: Write the test before to write the code');
    }

    public function testUnlockBadgeIfNotAlreadyOwner(): void
    {
        $this->markTestIncomplete('TODO: Write the test before to write the code');
    }
}
