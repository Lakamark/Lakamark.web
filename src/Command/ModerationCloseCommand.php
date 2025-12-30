<?php

namespace App\Command;

use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Repository\UserBanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:moderation:close',
    description: 'To close a expired moderation ban.',
)]
class ModerationCloseCommand extends Command
{
    public function __construct(
        private readonly UserBanRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTimeImmutable();
        $bans = $this->repository->findExpiredNotEnded($now);

        /** @var UserBan $ban */
        foreach ($bans as $ban) {
            $ban->endByExpiration();
        }

        $this->em->flush();

        $io->success(sprintf('Closed %d expired bans.', count($bans)));

        return Command::SUCCESS;
    }
}
