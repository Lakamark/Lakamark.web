<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractCommand extends Command
{
    public function __construct(
        protected readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    /**
     * The children class can define with environment is allowed to run the command.
     *
     * @example ['dev','test','prod']
     *
     * @return string[]
     */
    abstract protected function allowedEnvironments(): array;

    /**
     * We check the environment before to run a command.
     * By default, we reject to run a command,
     * If you forgot to define with environment is allowed via allowedEnvironments() method.
     *
     * Sometime, I run a dangerous command by accidentally...
     */
    protected function checkEnv(SymfonyStyle $io, string $commandName): bool
    {
        $env = $this->kernel->getEnvironment();
        $allowedEnvironments = $this->allowedEnvironments();

        if (!in_array($env, $allowedEnvironments, true)) {
            $io->error(sprintf(
                'Command "%s" not allowed in "%s". Allowed: %s',
                $commandName,
                $env,
                implode(', ', $allowedEnvironments)
            ));

            return false;
        }

        return true;
    }
}
