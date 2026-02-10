<?php

namespace App\Foundation\Captcha\Puzzle;

use App\Foundation\Captcha\Contract\CaptchaChallengeInterface;
use App\Foundation\Captcha\Contract\CaptchaSolutionStoreInterface;
use Random\RandomException;

class PuzzleCaptchaChallenge implements CaptchaChallengeInterface
{
    public const int WIDTH = 300;
    public const int HEIGHT = 200;
    public const int PIECE_WIDTH = 80;
    public const int PIECE_HEIGHT = 50;
    private const int POSITION_TOLERANCE_PX = 5;
    private const int TTL_SECONDS = 300; // 5 minutes

    public function __construct(
        private readonly CaptchaSolutionStoreInterface $store,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function generateKey(): string
    {
        $key = bin2hex(random_bytes(16));

        // Generate random position for puzzle piece.
        $minX = 0;
        $maxX = self::WIDTH - self::PIECE_WIDTH;

        $minY = 0;
        $maxY = self::HEIGHT - self::PIECE_HEIGHT;

        $solution = [
            'x' => random_int($minX, $maxX),
            'y' => random_int($minY, $maxY),
        ];

        $this->store->put($key, $solution, self::TTL_SECONDS);

        return $key;
    }

    public function verify(string $key, string $answer): bool
    {
        $expected = $this->store->get($key);

        if (!is_array($expected) || !isset($expected['x'], $expected['y'])) {
            return false;
        }

        $payload = json_decode($answer, true);
        if (!is_array($payload) || !isset($payload['x'], $payload['y'])) {
            return false;
        }

        $actualX = filter_var($payload['x'], FILTER_VALIDATE_INT);
        $actualY = filter_var($payload['y'], FILTER_VALIDATE_INT);

        if (false === $actualX || false === $actualY) {
            return false;
        }

        // Delete the solution in the session.
        $this->store->delete($key);

        return
            abs($expected['x'] - $actualX) <= self::POSITION_TOLERANCE_PX
            && abs($expected['y'] - $actualY) <= self::POSITION_TOLERANCE_PX;
    }

    public function getSolution(string $key): mixed
    {
        return $this->store->get($key);
    }
}
