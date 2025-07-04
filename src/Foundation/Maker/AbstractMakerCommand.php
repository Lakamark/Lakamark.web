<?php

namespace App\Foundation\Maker;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;

abstract class AbstractMakerCommand extends Command
{
    public function __construct(
        private Environment $twig,
        protected string $projectDir,
    ) {
        parent::__construct();
    }

    protected function createFiles(string $template, array $params, string $output): void
    {
        $content = $this->twig->render("@maker/$template.twig", $params);
        $filename = "$this->projectDir/$output";
        if (!file_exists($filename)) {
            mkdir($filename, 0777, true);
        }
        file_put_contents($filename, $content);
    }

    protected function askClass(string $question, string $pattern, SymfonyStyle $io, bool $multiple = false): array
    {
        $classes = [];
        $paths = explode('/', $pattern);
        if (1 === count($paths)) {
            $directory = "{$this->projectDir}/src";
            $pattern = $pattern;
        } else {
            $directory = "{$this->projectDir}/src/".join('/', array_slice($paths, 0, -1));
            $pattern = join('/', array_slice($paths, -1));
        }
        $files = (new Finder())->in($directory)->name($pattern.'.php')->files();
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $filename = str_replace('.php', '', $file->getBasename());
            $classes[$filename] = $file->getPathname();
        }

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

    protected function askDomain(SymfonyStyle $io): string
    {
        $domains = [];
        $files = (new Finder())->in("{$this->projectDir}/src/Domain")->depth(0)->directories();
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $domains[] = $file->getBasename();
        }

        $q = new ChoiceQuestion('Select a domain', $domains);

        return $io->askQuestion($q);
    }
}
