<?php

namespace App\Foundation\Maker;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand('create:domain')]
class MakerDomainCommand extends AbstractMakerCommand
{
    /**
     * The domain structure.
     *
     * @var string[]
     */
    private array $directories = [
        'Entity',
        'Repository',
        'Event',
        'EventListener',
        'Service',
        'Subscriber',
    ];

    protected function configure(): void
    {
        $this
            ->setDescription('Create a new domain')
            ->addArgument('domainName', InputArgument::REQUIRED, 'Domain name')
            ->addOption('full', null, InputOption::VALUE_OPTIONAL, 'Create full structure domain', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $fileSystem = new Filesystem();

        /** @var string $domain */
        $domain = $input->getArgument('domainName');

        $domineRoot = "$this->projectDir/Domain/$domain";

        $fullOptions = $input->getOption('full');
        dd("$domineRoot", $fullOptions);

        $io->success('Domain created successfully');

        return Command::SUCCESS;
    }
}
