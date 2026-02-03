<?php

namespace App\Command;

use App\Domain\Moderation\Service\ModerationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:moderation:close',
    description: 'Close all active bans.',
)]
class CloseExpiredBansCommand extends AbstractCommand
{
    public function __construct(
        KernelInterface $kernel,
        private readonly ModerationService $moderationService,
    ) {
        parent::__construct($kernel);
    }

    protected function allowedEnvironments(): array
    {
        return ['prod'];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->checkEnv($io, $input)) {
            return Command::FAILURE;
        }

        $count = $this->moderationService->closeExpiredBans(new \DateTimeImmutable());

        $io->success(sprintf('Closed %d expired ban(s).', $count));

        return Command::SUCCESS;
    }
}
