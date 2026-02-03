<?php

namespace App\Command;

use App\Domain\Auth\Repository\UserRepository;
use App\Domain\Moderation\Exception\CannotUnbanBotUserException;
use App\Domain\Moderation\Service\ModerationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:moderation:unban',
    description: 'Close an active ban for a user',
)]
class UnbanUserCommand extends AbstractCommand
{
    public function __construct(
        KernelInterface $kernel,
        private readonly UserRepository $userRepository,
        private readonly ModerationService $moderationService,
    ) {
        parent::__construct($kernel);
    }

    protected function configure(): void
    {
        $this->addArgument('userId', InputOption::VALUE_REQUIRED, 'User ID');
    }

    protected function allowedEnvironments(): array
    {
        return ['dev', 'prod', 'test'];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->checkEnv($io, $input)) {
            return Command::FAILURE;
        }

        $userId = $input->getArgument('userId');

        if (!$userId) {
            $io->error('Missing required option: --userId');

            return Command::FAILURE;
        }

        $user = $this->userRepository->find($userId);

        if (!$user) {
            $io->error('User not found');

            return Command::FAILURE;
        }

        // Call the ModerationService
        try {
            $unBanned = $this->moderationService->unbanUser($user, new \DateTimeImmutable());
        } catch (CannotUnbanBotUserException) {
            $io->error('Cannot unban a BOT ban.');

            return Command::FAILURE;
        }

        if (!$unBanned) {
            $io->warning(sprintf(
                'No active ban found for user %s.',
                $userId
            ));

            return Command::SUCCESS;
        }

        $io->success(sprintf('User %s has been unbanned.', $userId));

        return Command::SUCCESS;
    }
}
