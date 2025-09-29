<?php

namespace App\Domain\Captcha\Puzzle;

use App\Domain\Captcha\CaptchaChallengeInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PuzzleChallenge implements CaptchaChallengeInterface
{
    public const WIDTH = 350;
    public const HEIGHT = 200;
    public const PIECE_WIDTH = 80;
    public const PIECE_HEIGHT = 50;
    private const SESSION_KEY = 'PUZZLE_CAPTCHA';
    private const PRECISION = 5;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * To generate the challenge key and to store in the session.
     */
    public function generateKey(): string
    {
        $session = $this->getSession();
        $now = time();
        $x = mt_rand(0, self::WIDTH - self::PIECE_WIDTH);
        $y = mt_rand(0, self::HEIGHT - self::PIECE_HEIGHT);
        $puzzles = $session->get(self::SESSION_KEY, []);
        $puzzles[] = ['key' => $now, 'solution' => [$x, $y]];
        $session->set(self::SESSION_KEY, array_slice($puzzles, -10));

        return $now;
    }

    /**
     * To very if the user answer match with the expected answer.
     */
    public function verify(string $key, string $answer): bool
    {
        // The expected answer
        $expected = $this->getSolution($key);

        // If the key is invalid we can check the captcha.
        if (!$expected) {
            return false;
        }

        // Delete the puzzle from the session.
        // To avoid the user can submit many times the same puzzle challenge.
        $session = $this->getSession();
        $puzzles = $session->get(self::SESSION_KEY);
        $session->set(self::SESSION_KEY, array_filter($puzzles, fn (array $puzzle) => $puzzle['key'] !== intval($key)));

        // The user answer
        $got = $this->stringToPosition($answer);

        return abs($expected[0] - $got[0]) <= self::PRECISION && abs($expected[1] - $got[1]) <= self::PRECISION;
    }

    /**
     * @return int[]|null
     */
    public function getSolution(string $Key): ?array
    {
        $puzzles = $this->getSession()->get(self::SESSION_KEY, []);
        foreach ($puzzles as $puzzle) {
            if ($puzzle['key'] !== intval($Key)) {
                continue;
            }

            return $puzzle['solution'];
        }

        return null;
    }

    /**
     * To convert a string value to an array.
     * From '100-100' to [100, 100].
     * Is easier to pass the value to a custom constraint validator.
     *
     * @return int[]
     */
    private function stringToPosition(string $s): array
    {
        $parts = explode('-', $s, 2);
        if (2 !== count($parts)) {
            return [-1, -1];
        }

        return [intval($parts[0]), intval($parts[1])];
    }

    /**
     * We can't use dependency injection for SessionInterface in Symfony.
     * We use the RequestStack class to get the session.
     * We can work with the session to storage some key in the current session.
     */
    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
