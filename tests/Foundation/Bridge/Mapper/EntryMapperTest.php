<?php

namespace App\Tests\Foundation\Bridge\Mapper;

use App\Foundation\Bridge\Mapper\EntryMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EntryMapperTest extends TestCase
{
    #[DataProvider('provideMappings')]
    public function testMap(string $entry, string $expected): void
    {
        $mapper = new EntryMapper();
        
        $this->assertSame($expected, $mapper->map($entry));
    }
    
    public static function provideMappings(): iterable
    {
        yield 'logical app entry' => [
            'app',
            'app.ts',
        ];
        
        yield 'logical dashboard entry' => [
            'dashboard',
            'dashboard.ts',
        ];
        
        yield 'explicit ts entry is preserved' => [
            'feature.ts',
            'feature.ts',
        ];
        
        yield 'explicit js entry is preserved' => [
            'legacy.js',
            'legacy.js',
        ];
        
        yield 'nested ts entry is preserved' => [
            'admin/panel.ts',
            'admin/panel.ts',
        ];
    }
}