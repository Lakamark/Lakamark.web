<?php

namespace App\Tests\Foundation\Bridge;

use App\Foundation\Bridge\SimpleEntryMapper;
use PHPUnit\Framework\TestCase;

class EntryMapperTest extends TestCase
{
    public function testMapLogicalEntryToAssetPath(): void
    {
        $mapper = new SimpleEntryMapper();
        $this->assertSame('app.js', $mapper->map('assets/app.js'));
        $this->assertSame('app.js', $mapper->map('/assets/app.js'));
        $this->assertSame('app.js', $mapper->map('app.js'));
    }
}
