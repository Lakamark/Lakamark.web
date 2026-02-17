<?php

namespace App\Tests\Http\Twig;

use App\Http\Twig\iconRendererTwigExtension;
use PHPUnit\Framework\TestCase;

class iconRendererTwigExtensionTest extends TestCase
{
    private readonly iconRendererTwigExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new iconRendererTwigExtension();
    }

    public function testToRendHtmlSvgIcon(): void
    {
        $expected = <<<HTML
        <svg class="icon-element icon-logo">
            <use href="/images/icons.svg#logo"></use>
        </svg>
        HTML;
        $this->assertSame($expected, $this->extension->svgIcon('logo'));
    }

    public function testToRendHtmlSvgIconWithSize(): void
    {
        $expected = <<<HTML
        <svg class="icon-element icon-logo" width="80px" height="80px">
            <use href="/images/icons.svg#logo"></use>
        </svg>
        HTML;
        $this->assertSame($expected, $this->extension->svgIcon('logo', 80));
    }

    public function testToRendHtmlSvgIconWithACustomClass(): void
    {
        $expected = <<<HTML
        <svg class="custom-class">
            <use href="/images/icons.svg#logo"></use>
        </svg>
        HTML;
        $this->assertSame($expected, $this->extension->svgIcon('logo', null, 'custom-class'));
    }

    public function testToRendHtmlSvgIconFullCustomOptions(): void
    {
        $expected = <<<HTML
        <svg class="custom-class" width="80px" height="80px">
            <use href="/images/icons.svg#logo"></use>
        </svg>
        HTML;
        $this->assertSame($expected, $this->extension->svgIcon('logo', 80, 'custom-class'));
    }
}
