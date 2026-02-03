<?php

namespace App\Tests\Command;

use App\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Command used to validate the AbstractCommand behavior.
 */
final class ClaptrapCommand extends AbstractCommand
{
    public bool $executed = false;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);
    }

    protected function allowedEnvironments(): array
    {
        return ['dev'];
    }

    protected function supportsForce(): bool
    {
        return true;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (!$this->checkEnv($io, $input)) {
            return Command::FAILURE;
        }

        $this->executed = true;

        return Command::SUCCESS;
    }
}
