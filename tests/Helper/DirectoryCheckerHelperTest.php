<?php

namespace App\Tests\Helper;

use App\Helper\DirectoryCheckerHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class DirectoryCheckerHelperTest extends TestCase
{
    public function testDirectoryNotExit(): void
    {
        $this->expectException(DirectoryNotFoundException::class);
        DirectoryCheckerHelper::isDir('Test');
    }

    public function testDirectoryExit(): void
    {
        $this->assertTrue(DirectoryCheckerHelper::isDir('tests'));
        $this->assertTrue(DirectoryCheckerHelper::isDir('tests/Domain'));
    }
}
