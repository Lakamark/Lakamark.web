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

#[AsCommand('app:domain:create')]
final class DomainMakerCommand extends AbstractMakerCommand
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
            ->setDescription('Create a new domain.')
            ->addArgument('domainName', InputArgument::REQUIRED, 'Domain name')
            ->addOption('full', '-f', InputOption::VALUE_NEGATABLE, 'create all domain structure? (Entity, Repository, Event, Listener, Service, Subscriber)',
                false);
    }

    protected function canCreateNewDomain(): bool
    {
        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $domain */
        $domain = $input->getArgument('domainName');

        $domineRoot = "$this->rootProjectDir/src/Domain/$domain";

        $fullOptionValue = $input->getOption('full');
        $this->generateAllFolders($domineRoot, $fullOptionValue);

        $io->success("The $domain Domain created successfully");

        return Command::SUCCESS;
    }

    /**
     * If you want to create all folders (Entity, Repository, Services etc.)
     * or just an empty domaine if you want to create your own domain structure.
     */
    private function generateAllFolders(string $path, bool $multiple = false): void
    {
        $fileSystem = new Filesystem();
        if (false === $multiple) {
            $fileSystem->mkdir($path);
        } else {
            foreach ($this->directories as $directory) {
                $fileSystem->mkdir("$path/$directory");
            }
        }
    }
}
