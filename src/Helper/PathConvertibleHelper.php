<?php

namespace App\Helper;

class PathConvertibleHelper
{
    /**
     * To convert slashes "\\" in url to / or \ depend on your hardware.
     */
    public static function join(string ...$parts): string
    {
        return preg_replace(
            '~[/\\\\]+~',
            DIRECTORY_SEPARATOR,
            implode(DIRECTORY_SEPARATOR, $parts)
        ) ?: '';
    }
}
