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
        protected readonly string $rootProjectDir,
        private readonly Environment $twig,
    ) {
        parent::__construct();
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function createFile(string $templatePath, array $params, string $outFile): void
    {
        $content = $this->twig->render("@maker/$templatePath.twig", $params);
        $filename = "{$this->rootProjectDir}/$outFile";
        $directory = dirname($filename);

        // If the directory doesn't exist create it.
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($filename, $content);
    }

    /**
     * To generate a class in a domain.
     */
    protected function askForClass(string $question, string $pattern, SymfonyStyle $io, bool $multiple = false): array
    {
        // Prepare classes array and explod the pattern.
        $classes = [];
        $paths = explode('/', $pattern);

        // If the path is src root keep the same defined pattern (src/myClass.php)
        // Else we create into a folder via indexes pattern.
        if (1 === count($paths)) {
            $directory = "$this->rootProjectDir/src";
        } else {
            $directory = "{$this->rootProjectDir}/src/".join('/', array_slice($paths, 0, -1));
            $pattern = join('/', array_slice($paths, -1));
        }

        // Generate PHP files in the selected directory.
        $files = (new Finder())
            ->in($directory)
            ->name($pattern.'.php')
            ->files();

        /**
         * File the classes array.
         *
         * @var SplFileInfo $file
         */
        foreach ($files as $file) {
            $filename = str_replace('.php', '', $file->getBasename());
            $classes[$filename] = $file->getPathname();
        }

        // Ask the question.
        $q = new Question($question);
        $q->setAutocompleterValues(array_keys($classes));

        // Prepare the answers array for the command.
        $answers = [];
        $replacements = [
            "$this->rootProjectDir/src" => 'App',
            '/' => '\\',
            '.php' => '',
        ];

        // If the multiple option is enabled we create many files,
        // otherwise return first answer index.
        while (true) {
            $class = $io->askQuestion($q);

            if (null === $class) {
                return $answers; // Return the answer array.
            }

            $path = $classes[$class];

            // File the answers array.
            $answers[] = [
                'namespace' => str_replace(array_keys($replacements), array_values($replacements), $path),
                'class_name' => $class,
            ];

            // If the function is called one time,
            // We return the first index in the answer array.
            if (false === $multiple) {
                return $answers[0];
            }
        }
    }

    /**
     * To display a domain list available in the application.
     */
    protected function askForDomain(SymfonyStyle $io): string
    {
        $domains = [];
        $root = "$this->rootProjectDir/src/Domain";
        $files = (new Finder())
            ->in($root)
            ->depth(0)
            ->directories();

        /**
         * We file the domain list to display to the user.
         *
         * @var SplFileInfo $file
         */
        foreach ($files as $file) {
            $domains[] = $file->getBasename();
        }

        // Ask the question
        $q = new ChoiceQuestion('Which domain would you like to use?', $domains, 0);
        $q->setAutocompleterValues($domains);

        return $io->askQuestion($q);
    }
}
