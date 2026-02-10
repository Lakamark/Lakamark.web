<?php

namespace App\Foundation\Captcha\Puzzle;

use App\Foundation\Captcha\Contract\CaptchaChallengeInterface;
use App\Foundation\Captcha\Contract\CaptchaGeneratorInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class PuzzleGenerator implements CaptchaGeneratorInterface
{
    public function __construct(
        private array $images,
        private CaptchaChallengeInterface $captchaChallenge,
    ) {
    }

    public function generate(string $key): Response
    {
        $solution = $this->captchaChallenge->getSolution($key);

        if (!is_array($solution) || !isset($solution['x'], $solution['y'])) {
            return new Response('Invalid captcha key', 404);
        }

        $imagePath = $this->pickRandomImage();

        // ⚠️ Install Intervention Image
        // $img = Image::make($imagePath);
        // draw puzzle hole at ($solution['x'], $solution['y'])

        return new BinaryFileResponse($imagePath, 200);

        /*
         * We will use StreamResponse later.
         * return new StreamedResponse(function () use ($image) {
         * echo $image->encode('png');
         * }, 200, ['Content-Type' => 'image/png']);
         */
    }

    private function pickRandomImage(): string
    {
        return $this->images[array_rand($this->images)];
    }
}
