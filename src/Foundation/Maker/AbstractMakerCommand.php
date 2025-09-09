<?php

namespace App\Foundation\Maker;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class AbstractMakerCommand extends Command
{
    public function __construct(
        private readonly Environment $twig,
        protected string $projectDir,
    ) {
        parent::__construct();
    }

    /**
     * To create files (A controller, event classes etc.).
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function generateFile(string $templatePath, array $params, string $outputFile): void
    {
        $content = $this->twig->render("@maker/$templatePath.twig", $params);
        $filename = "{$this->projectDir}/$outputFile";
        $directory = dirname($filename);
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($filename, $content);
    }

    protected function askClass(string $question, string $pattern, SymfonyStyle $io, bool $multiple = false): array
    {
        $classes = [];
        $paths = explode('/', $pattern);

        if (1 === count($paths)) {
            $directory = "$this->projectDir/src";
            $pattern = $pattern;
        } else {
            $directory = "{$this->projectDir}/src/".join('/', array_slice($paths, 0, -1));
            $pattern = join('/', array_slice($paths, -1));
        }

        $files = (new Finder())
            ->in($directory)
            ->name($pattern.'.php')
            ->files();
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $filename = str_replace('.php', '', $file->getBasename());
            $classes[$filename] = $file->getPathname();
        }

        // Ask the question
        $q = new Question($question);
        $q->setAutocompleterValues(array_keys($classes));
        $answers = [];
        $replacements = [
            "{$this->projectDir}/src" => 'App',
            '/' => '\\',
            '.php' => '',
        ];

        while (true) {
            $class = $io->askQuestion($q);
            if (null === $class) {
                return $answers;
            }
            $path = $classes[$class];

            $answers[] = [
                'namespace' => str_replace(array_keys($replacements), array_values($replacements), $path),
                'class_name' => $class,
            ];
            if (false === $multiple) {
                return $answers[0];
            }
        }
    }

    /**
     * To display a domain list available in the application.
     */
    protected function askSelectDomain(SymfonyStyle $io): string
    {
        // We create a domain list available in the application (Application, Blog, Projects etc.)
        $domainList = [];
        $files = (new Finder())
            ->in("$this->projectDir/src/Domain")
            ->depth(0)
            ->directories();

        // We put the domains in the list
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $domainList[] = $file->getBasename();
        }

        // Ask the question
        $q = new ChoiceQuestion('Please select a domain:', $domainList, 0);
        $q->setAutocompleterValues($domainList);

        return $io->askQuestion($q);
    }
}
