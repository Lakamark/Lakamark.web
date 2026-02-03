<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractCommand extends Command
{
    public function __construct(
        protected readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force the operation to run when in production mode.',
        );
    }

    /**
     * @return list<'dev'|'test'|'prod'>
     */
    abstract protected function allowedEnvironments(): array;

    /**
     * Override if the command supports --force.
     */
    protected function supportsForce(): bool
    {
        return false;
    }

    /**
     * We check the environment before to run a command.
     * By default, we reject to run a command,
     * If you forgot to define with environment is allowed via allowedEnvironments() method.
     *
     * Sometime, I run a dangerous command by accidentally...
     */
    protected function checkEnv(SymfonyStyle $io, InputInterface $input): bool
    {
        $env = $this->kernel->getEnvironment();
        $allowedEnvironments = $this->allowedEnvironments();

        // --force guard in prod mode only
        if ($input->getOption('force')) {
            if (!$this->supportsForce()) {
                $io->error('This command does not support --force.');

                return false;
            }

            if ('prod' !== $env) {
                $io->error('--force is only allowed in prod environment.');

                return false;
            }

            return true;
        }

        // Check if the current env is defined in the allowed environment array.
        if (!in_array($env, $allowedEnvironments, true)) {
            $io->error(sprintf(
                'Command "%s" not allowed in "%s". Allowed: %s',
                $this->getName(),
                $env,
                implode(', ', $allowedEnvironments)
            ));

            return false;
        }

        return true;
    }
}
