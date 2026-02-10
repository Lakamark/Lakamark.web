<?php

namespace App\Tests\Foundation\Captcha\Puzzle;

use App\Foundation\Captcha\Puzzle\PuzzleCaptchaChallenge;
use App\Foundation\Captcha\Store\InMemoryCaptchaSolutionStore;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

class PuzzleCaptchaChallengeTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function testGenerateKeyStoresSolutionAsXY(): void
    {
        $store = new InMemoryCaptchaSolutionStore();
        $challenge = new PuzzleCaptchaChallenge($store);

        $key = $challenge->generateKey();
        $this->assertNotSame('', $key);

        $solution = $store->get($key);

        $this->assertIsArray($solution);
        $this->assertArrayHasKey('x', $solution);
        $this->assertArrayHasKey('y', $solution);
        $this->assertIsInt($solution['x']);
        $this->assertIsInt($solution['y']);
    }

    /**
     * @throws \JsonException
     */
    public function testVerifyReturnsTrueWhenWithinTolerance(): void
    {
        $store = new InMemoryCaptchaSolutionStore();
        $challenge = new PuzzleCaptchaChallenge($store);

        $key = 'k';
        $store->put($key, ['x' => 100, 'y' => 50], 300);

        $answer = json_encode(['x' => 104, 'y' => 46], JSON_THROW_ON_ERROR); // delta 4
        $this->assertTrue($challenge->verify($key, $answer));
    }

    /**
     * @throws \JsonException
     */
    public function testVerifyReturnsFalseWhenOutsideTolerance(): void
    {
        $store = new InMemoryCaptchaSolutionStore();
        $challenge = new PuzzleCaptchaChallenge($store);

        $key = 'k';
        $store->put($key, ['x' => 100, 'y' => 50], 300);

        $answer = json_encode(['x' => 106, 'y' => 50], JSON_THROW_ON_ERROR); // delta 6
        $this->assertFalse($challenge->verify($key, $answer));
    }

    public function testVerifyReturnsFalseWhenAnswerIsInvalidJson(): void
    {
        $store = new InMemoryCaptchaSolutionStore();
        $challenge = new PuzzleCaptchaChallenge($store);

        $key = 'k';
        $store->put($key, ['x' => 100, 'y' => 50], 300);

        $this->assertFalse($challenge->verify($key, 'not-json'));
    }

    /**
     * @throws \JsonException
     */
    public function testVerifyReturnsFalseWhenKeyNotFound(): void
    {
        $store = new InMemoryCaptchaSolutionStore();
        $challenge = new PuzzleCaptchaChallenge($store);

        $answer = json_encode(['x' => 1, 'y' => 1], JSON_THROW_ON_ERROR);
        $this->assertFalse($challenge->verify('missing', $answer));
    }

    /**
     * @throws \JsonException
     */
    public function testVerifyDeletesSolutionAfterAttempt(): void
    {
        $store = new InMemoryCaptchaSolutionStore();
        $challenge = new PuzzleCaptchaChallenge($store);

        $key = 'k';
        $store->put($key, ['x' => 100, 'y' => 50], 300);

        $answer = json_encode(['x' => 100, 'y' => 50], JSON_THROW_ON_ERROR);
        $challenge->verify($key, $answer);

        $this->assertNull($store->get($key));
    }
}
