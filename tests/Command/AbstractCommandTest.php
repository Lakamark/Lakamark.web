<?php

namespace App\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class AbstractCommandTest extends TestCase
{
    public function testCheckEnvReturnsFalseWhenEnvNotAllowed(): void
    {
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('prod');

        $command = new ClaptrapCommand($kernel);
        $tester = new CommandTester($command);
        $status = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertFalse($command->executed);
    }

    public function testCommandIsBlockedForUnknownEnvironment(): void
    {
        $kernel = $this->createStub(KernelInterface::class);

        // Test an unknow environment.
        $kernel->method('getEnvironment')->willReturn('Butt Stallion');

        $command = new ClaptrapCommand($kernel);
        $tester = new CommandTester($command);
        $status = $tester->execute([]);
        $output = $tester->getDisplay();

        $this->assertSame(Command::FAILURE, $status);
        $this->assertFalse($command->executed);
        $this->assertStringContainsString('not allowed', $output);
    }

    public function testCommandExecutesWhenEnvAllowed(): void
    {
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('dev');

        $command = new ClaptrapCommand($kernel);
        $tester = new CommandTester($command);
        $status = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertTrue($command->executed);
    }

    public function testForceIsRejectedOutsideProd(): void
    {
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('dev');

        $command = new ClaptrapCommand($kernel);
        $tester = new CommandTester($command);
        $status = $tester->execute(['--force' => true]);

        $this->assertSame(Command::FAILURE, $status);
    }

    public function testForceIsAllowedInProd(): void
    {
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('prod');

        $command = new ClaptrapCommand($kernel);
        $tester = new CommandTester($command);
        $status = $tester->execute(['--force' => true]);

        $this->assertSame(Command::SUCCESS, $status);
    }
}
