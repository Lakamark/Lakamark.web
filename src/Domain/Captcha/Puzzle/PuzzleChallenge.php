<?php

namespace App\Domain\Captcha\Puzzle;

use App\Domain\Captcha\ChallengeInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PuzzleChallenge implements ChallengeInterface
{
    public const WIDTH = 350;
    public const HEIGHT = 200;
    public const PIECE_WIDTH = 80;
    public const PIECE_HEIGHT = 50;
    private const SESSION_KEY = 'PUZZLE_CAPTCHA';
    private const ERROR_MARGE = 5;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function generateKey(): string
    {
        $session = $this->getSession();
        $now = time();
        $x = mt_rand(0, self::WIDTH - self::PIECE_WIDTH);
        $y = mt_rand(0, self::HEIGHT - self::PIECE_HEIGHT);

        // Save the session puzzles
        $puzzle = $session->get(self::SESSION_KEY, []);
        $puzzle[] = ['key' => $now, 'solution' => [$x, $y]];
        $session->set(self::SESSION_KEY, array_slice($puzzle, -10));

        // Return the key
        return $now;
    }

    public function verify(string $key, string $answer): bool
    {
        // Expected solution
        $expected = $this->getSolution($key);

        // If the challenge key is invalid
        if (!$expected) {
            return false;
        }

        // Delete the session the puzzle challenge
        $session = $this->getSession();
        $puzzles = $session->get(self::SESSION_KEY);
        $session->set(self::SESSION_KEY, array_filter($puzzles, fn ($puzzle) => $puzzle['key'] !== intval($key)));

        // The user answer
        $got = $this->stringToPosition($answer);

        return abs($expected[0] - $got[0]) <= self::ERROR_MARGE
            && abs($expected[1] - $got[1]) <= self::ERROR_MARGE;
    }

    /**
     * @return int[]|null
     */
    public function getSolution(string $challengeKey): ?array
    {
        $puzzles = $this->getSession()->get(self::SESSION_KEY, []);
        foreach ($puzzles as $puzzle) {
            if ($puzzle['key'] !== intval($challengeKey)) {
                continue;
            }

            return $puzzle['solution'];
        }

        return null;
    }

    /**
     * Return an array position (x,y) from a string.
     * from 100-100 to [100,100].
     *
     * @return int[]
     */
    private function stringToPosition(string $s): array
    {
        $parts = explode('-', $s, 2);

        // If the integer array is more thant 2 values. We can't generate (x, y) positions
        if (2 !== count($parts)) {
            return [-1, -1];
        }

        return [intval($parts[0]), intval($parts[1])];
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
