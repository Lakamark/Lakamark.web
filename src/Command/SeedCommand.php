<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:db:seed',
    description: 'Seed the database with all tables.',
)]
class SeedCommand extends AbstractCommand
{
    public function __construct(
        KernelInterface $kernel,
    ) {
        parent::__construct($kernel);
    }

    /**
     * Dangerous command – database destructive.
     */
    protected function allowedEnvironments(): array
    {
        return ['dev', 'test'];
    }

    /**
     * To run the hautlok command and to clean the database.
     *
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $app = $this->getApplication();

        // To ensure we are on a dev environment.
        if (!$this->checkEnv($io, $input)) {
            return Command::FAILURE;
        }

        // Ensure the Application is initialized before to run a command.
        if (null === $app) {
            $io->error('Application not initialized.');

            return Command::FAILURE;
        }

        // Call  the hautlook command behind
        $command = $app->find('hautelook:fixtures:load');

        // Set the command parameters and to clean the database.
        // Type this command : php bin/console hautelook:fixtures:load --help
        // to lean more...
        $commandParams = new ArrayInput([
            'command' => 'hautelook:fixtures:load',
            '--no-interaction' => true,
            '--purge-with-truncate' => true,
        ]);

        $io->note('Running fixtures with TRUNCATE purge (IDs will reset).');

        $outPutCommand = $command->run($commandParams, $output);

        // If something wrong, we return the output command for debug purpose.
        if (Command::SUCCESS !== $outPutCommand) {
            return $outPutCommand;
        }

        $io->success('Your database is seeded!');

        return Command::SUCCESS;
    }
}
