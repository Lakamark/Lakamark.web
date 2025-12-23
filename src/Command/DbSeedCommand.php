<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'db:seed',
    description: 'Seed the database with test data.',
)]
class DbSeedCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $app = $this->getApplication();

        // Call  the hautlook command behind
        $command = $app->find('hautelook:fixtures:load');
        $outPutCommand = $command->run($input, $output);

        // If something wrong, we return the output command for debug purpose.
        if (Command::SUCCESS !== $outPutCommand) {
            return $outPutCommand;
        }

        $io->success('Your database is seeded!');

        return Command::SUCCESS;
    }
}
