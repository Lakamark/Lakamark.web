<?php

namespace App\Domain\Captcha\Puzzle;

use App\Domain\Captcha\ChallengeGeneratorInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Response;

class PuzzleGenerator implements ChallengeGeneratorInterface
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
        $this->holeImage = $imagePath.'/puzzle-hole.png';
    }

    /**
     * TODO Finish to write this code.
     */
    public function generate(string $challengeKey): Response
    {
        $position = $this->puzzleChallenge->getSolution($challengeKey);

        // If the position are invalid
        if (null === $position) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        [$x, $y] = $position;

        // Init Intervention Library
        $manager = new ImageManager(new Driver());

        $image = $manager->read($this->backgroundImage);

        return new Response('Hello world!', Response::HTTP_OK, []);
    }
}
