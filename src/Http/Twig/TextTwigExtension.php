<?php

namespace App\Http\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * An extension to help to modify some text in your application.
 */
class TextTwigExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('excerpt', $this->excerpt(...)),
        ];
    }

    /**
     * To create an except from a long text.
     */
    public function excerpt(?string $content, int $maxLength = 250): string
    {
        if (is_null($content)) {
            return '';
        }

        if (mb_strlen($content) > $maxLength) {
            $excerpt = mb_substr($content, 0, $maxLength);
            $lastSpace = mb_strrpos($excerpt, ' ');

            return mb_substr($excerpt, 0, $lastSpace).'...';
        }

        return $content;
    }
}
