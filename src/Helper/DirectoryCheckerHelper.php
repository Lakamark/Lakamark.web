<?php

namespace App\Helper;

use Symfony\Component\Finder\Finder;

class DirectoryCheckerHelper
{
    /**
     * To check if the directory exist.
     */
    public static function isDir(string $directory): bool
    {
        $finder = new Finder();
        $directories = $finder->directories()->in($directory);

        foreach ($directories as $directory) {
            if ($directory->isDir()) {
                return true;
            }

            return false;
        }

        return false;
    }
}
