<?php

namespace App\Foundation\Maker;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsCommand(
    name: 'app:domain:enum',
    description: 'Create a enum class in a domain.',
)]
final class EnumMakerCommand extends AbstractMakerCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('enumName', InputArgument::OPTIONAL);
    }

    /**
     * To create an Enum class in your domain.
     *
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $domain = $this->askForDomain($io);
        $enumName = $input->getArgument('enumName');
        $root = "src/Domain/$domain/Enum/";

        // Define params to send to the template.
        $params = [
            'domainName' => $domain,
            'enumName' => $enumName,
        ];

        // Generate the file class.
        $this->createFile('enumClass', $params, "$root/$enumName.php");

        $io->success("Enum $enumName class created successfully in $root");

        return Command::SUCCESS;
    }
}
