<?php

namespace App\Tests\Command;

use App\Tests\TestCases\CommandTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CloseExpiredBansCommandTest extends CommandTestCase
{
    public function testExecute(): void
    {
        $command = $this->application->find('app:moderation:close');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
    }
}
