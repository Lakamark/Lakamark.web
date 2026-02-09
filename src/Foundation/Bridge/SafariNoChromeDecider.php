<?php

namespace App\Foundation\Bridge;

use App\Foundation\Bridge\Contract\UserAgentDeciderInterface;

final class SafariNoChromeDecider implements UserAgentDeciderInterface
{
    public function shouldLoadCustomElementsPolyfill(string $userAgent): bool
    {
        // Safari content "Safari" but Chrome content often "Safari" we will exclude Chrome/Chromium
        $ua = $userAgent;

        $isSafari = str_contains($ua, 'Safari');
        $isChromeLike = str_contains($ua, 'Chrome') || str_contains($ua, 'Chromium');

        return $isSafari && !$isChromeLike;
    }
}
