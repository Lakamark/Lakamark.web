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
    name: 'create:enum',
    description: 'Create a enum class in a domain.',
)]
class MakerEnumCommand extends AbstractMakerCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('enumName', InputArgument::OPTIONAL);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Ask to select a domain.
        $domain = $this->askSelectDomain($io);
        $enumName = $input->getArgument('enumName');
        $domainPath = "src/Domain/$domain/Enum/";

        // Define params to send to the template.
        $params = [
            'domainName' => $domain,
            'enumName' => $enumName,
        ];

        // Generate the file class.
        $this->generateFile('enumClass', $params, "$domainPath/$enumName.php");

        $io->success("The $enumName has been created. You can edit it.");

        return Command::SUCCESS;
    }
}
