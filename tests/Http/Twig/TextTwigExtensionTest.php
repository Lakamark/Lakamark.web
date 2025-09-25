<?php

namespace App\Tests\Http\Twig;

use App\Http\Twig\TextTwigExtension;
use PHPUnit\Framework\TestCase;

class TextTwigExtensionTest extends TestCase
{
    private readonly TextTwigExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new TextTwigExtension();
    }

    public function testTextExceptWithShortContent(): void
    {
        $content = 'Hello';
        $this->assertEquals($content, $this->extension->excerpt($content, 10));
    }

    public function testTextExceptWithLongContent(): void
    {
        $content = 'Hello everyone! How are you?';
        $this->assertEquals('Hello...', $this->extension->excerpt($content, 7));
        $this->assertEquals('Hello...', $this->extension->excerpt($content, 12));
    }
}
