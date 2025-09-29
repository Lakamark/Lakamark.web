<?php

namespace App\Domain\Captcha\Puzzle;

use App\Domain\Captcha\CaptchaGeneratorInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PuzzleGenerator implements CaptchaGeneratorInterface
{
    private string $backgroundImage;
    private string $holeImage;

    /**
     * @throws RandomException
     */
    public function __construct(
        string $imagePath,
        private readonly PuzzleChallenge $puzzleChallenge,
    ) {
        $randomNumber = random_int(1, 5);
        $this->backgroundImage = sprintf('%s/puzzle-%d.png', $imagePath, $randomNumber);
        $this->holeImage = $imagePath.'/puzzle_hole.png';
    }

    public function generate(string $key): Response
    {
        // Get the position from the key
        $position = $this->puzzleChallenge->getSolution($key);

        // If the position are invalid
        if (null === $position) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        [$x, $y] = $position;

        // Initialize the InterventionImage Manager Library.
        $manager = new ImageManager(new Driver());
        $image = $manager->read($this->backgroundImage);
        $imageWidth = $image->width();

        $hole = $manager->read($this->holeImage);

        // Piece
        $piece = $manager->read($this->holeImage);
        $piece->place($image, 'top-left', -$x, -$y);

        $image->place($hole, 'top-left', $x, $y);
        // Add the piece overlay
        $image->pad(
            $imageWidth + $piece->width(),
            $image->height(),
            'rgba(0,0,0,0)',
            'top-left'
        );

        $image->place($piece, 'top-left', $imageWidth);

        // return the puzzle to the client
        $stream = $image->toPng(indexed: true)->toFilePointer();

        return new StreamedResponse(function () use ($stream) {
            fpassthru($stream);
        }, Response::HTTP_OK, [
            'Content-Transfer-Encoding', 'binary',
            'Content-Type' => 'image/png',
            'Content-Length' => fstat($stream)['size'] ?? 0,
        ]);
    }
}
