<?php

namespace App\Command;

use App\Domain\Moderation\Service\ModerationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:moderation:close',
    description: 'Close the expired bans.'
)]
class CloseExpiredBansCommand extends Command
{
    public function __construct(
        private readonly ModerationService $moderationService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        return Command::SUCCESS;
    }
}
