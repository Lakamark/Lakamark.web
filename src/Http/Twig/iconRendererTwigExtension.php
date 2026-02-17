<?php

namespace App\Http\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * This is an icon render helper.
 * In your twig view you can use this syntax:
 *
 * @example {{ iconRenderer('logo') }}
 *
 * You can personalize the icon size:
 * @example {{ iconRenderer('logo', 80) }}
 *
 * You can use a custom CSS class rather than the default CSS class
 * @example {{ iconRenderer('logo', null, 'my-custom-class') }}
 */
class iconRendererTwigExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('iconRenderer', $this->svgIcon(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * To rend an icon from the sprite svg file.
     */
    public function svgIcon(string $iconName, ?int $size = null, ?string $customClass = null): string
    {
        // If you define a specific icon size.
        $attributes = '';
        if ($size) {
            $attributes = " width=\"{$size}px\" height=\"{$size}px\"";
        }

        // If you want to use a custom CSS class to personalize the icon.
        // Otherwise, we will generate a generic CSS class with ID icon.
        if ($customClass) {
            $class = "$customClass";
        } else {
            $class = 'icon-element icon-'.$iconName;
        }

        return <<<HTML
        <svg class="$class"$attributes>
            <use href="/images/icons.svg#$iconName"></use>
        </svg>
        HTML;
    }
}
