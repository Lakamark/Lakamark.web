<?php

namespace App\Tests\Helper;

use App\Helper\PathConvertibleHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PathConvertibleHelperTest extends TestCase
{
    public static function getPaths(): iterable
    {
        yield [['path1', 'path2', 'path3'], 'path1'.DIRECTORY_SEPARATOR.'path2'.DIRECTORY_SEPARATOR.'path3'];
        yield [['/path1', 'path2/', 'path3'], DIRECTORY_SEPARATOR.'path1'.DIRECTORY_SEPARATOR.'path2'.DIRECTORY_SEPARATOR.'path3'];
        yield [['/path1/aze/a', 'path2/', 'path3'], DIRECTORY_SEPARATOR.'path1'.DIRECTORY_SEPARATOR.'aze'
            .DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'path2'.DIRECTORY_SEPARATOR.'path3'];
    }

    #[DataProvider('getPaths')]
    public function testPathGeneration(array $parts, string $expected): void
    {
        $path = call_user_func_array(PathConvertibleHelper::join(...), $parts);
        $this->assertEquals($expected, $path);
    }
}
