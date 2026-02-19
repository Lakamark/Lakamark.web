<?php

namespace App\Foundation\Captcha\Puzzle;

use App\Foundation\Captcha\Contract\CaptchaChallengeInterface;
use App\Foundation\Captcha\Contract\CaptchaGeneratorInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class PuzzleGenerator implements CaptchaGeneratorInterface
{
    public function __construct(
        private string $rootImage,
        private CaptchaChallengeInterface $captchaChallenge,
        private int $imageCount = 5,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function generate(string $key): Response
    {
        $solution = $this->captchaChallenge->getSolution($key);

        if (!is_array($solution) || !isset($solution['x'], $solution['y'])) {
            return new Response('Invalid captcha key', Response::HTTP_NOT_FOUND);
        }

        $x = (int) $solution['x'];
        $y = (int) $solution['y'];

        $imagePath = $this->pickRandomImage();

        $manager = new ImageManager(new Driver());

        $image = $manager->read($imagePath);
        $imageWidth = $image->width();

        // Hole (overlay)
        $holeImage = "$this->rootImage/puzzle_hole.png";
        $hole = $manager->read($holeImage);

        // Dimensions de la pièce = dimensions du hole
        $pieceWidth = $hole->width();
        $pieceHeight = $hole->height();

        // Clamp
        $x = max(0, min($image->width() - $pieceWidth, $x));
        $y = max(0, min($image->height() - $pieceHeight, $y));

        // create a clone and crop the image piece.
        $piece = clone $image;
        $piece->crop($pieceWidth, $pieceHeight, $x, $y);

        // Hole
        $image->place($hole, 'top-left', $x, $y);

        $newWidth = $imageWidth + $piece->width();
        $image->pad(
            $newWidth,
            $image->height(),
            'rgba(0,0,0,0)',
            'top-left'
        );

        $image->place($piece, 'top-left', $imageWidth, 0);

        $stream = $image->toPng(indexed: true)->toFilePointer();

        return new StreamedResponse(function () use ($stream) {
            try {
                fpassthru($stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, Response::HTTP_OK, [
            'Content-Type' => 'image/png',
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * To choose a random puzzle list.
     *
     * @throws RandomException
     */
    private function pickRandomImage(): string
    {
        $n = random_int(1, $this->imageCount);

        return sprintf('%s/puzzle-%d.png', $this->rootImage, $n);
    }
}
