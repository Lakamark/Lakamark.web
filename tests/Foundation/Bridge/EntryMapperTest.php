<?php

namespace App\Tests\Foundation\Bridge;

use App\Foundation\Bridge\SimpleEntryMapper;
use PHPUnit\Framework\TestCase;

class EntryMapperTest extends TestCase
{
    public function testMapLogicalEntryToAssetPath(): void
    {
        $mapper = new SimpleEntryMapper('assets');
        $this->assertSame('assets/app.js', $mapper->map('app'));
        $this->assertSame('assets/admin.js', $mapper->map('admin'));
    }
}
