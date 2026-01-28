<?php

namespace App\Foundation\Maker;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:domain:entity',
    description: 'Create an entity in a domain.',
)]
final class EntityMakerCommand extends AbstractMakerCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('entityName', InputArgument::REQUIRED, 'The name of the entity.');
    }

    /**
     * To create an entity.
     *
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $domain = $this->askForDomain($io);

        /** @var string $entity */
        $entity = $input->getArgument('entityName');

        /** @var Application $application */
        $application = $this->getApplication();

        // We call the make entity command in the MakerBundle.
        $command = $application->find('make:entity');

        // Pass through the parameters to the command.
        $arguments = [
            'command' => 'make:entity',
            'name' => "\\App\\Domain\\$domain\\Entity\\$entity",
        ];

        $argumentInputs = new ArrayInput($arguments);

        return $command->run($argumentInputs, $output);
    }
}
