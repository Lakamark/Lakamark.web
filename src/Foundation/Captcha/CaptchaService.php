<?php

namespace App\Foundation\Captcha;

use App\Foundation\Captcha\Contract\CaptchaRegistryInterface;
use App\Foundation\Captcha\Contract\CaptchaVerifierInterface;
use App\Foundation\Captcha\Exception\CaptchaLockedException;
use App\Foundation\Captcha\Exception\CaptchaRuntimeException;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class CaptchaService implements CaptchaVerifierInterface
{
    /**
     * Maximum number of failed attempts before locking the captcha.
     */
    private const int LIMIT_TRIES = 3;
    private const int LOCK_SECONDS = 300;
    private const int MIN_SOLVE_SECONDS = 2;
    private const int MAX_SOLVE_SECONDS = 300;

    private const string SESSION_CURRENT_KEY = 'CAPTCHA_KEY';
    private const string SESSION_TYPE = 'CAPTCHA_TYPE';
    private const string SESSION_TRIES = 'CAPTCHA_TRIES';
    private const string SESSION_LOCKED_UNTIL = 'CAPTCHA_LOCKED_UNTIL';
    private const string SESSION_GENERATED_AT = 'CAPTCHA_GENERATED_AT';
    private const string SESSION_VERIFIED = 'CAPTCHA_VERIFIED';
    private const string SESSION_VERIFIED_TYPE = 'CAPTCHA_VERIFIED_TYPE';

    public function __construct(
        private readonly CaptchaRegistryInterface $registry,
        private readonly RequestStack $requestStack,
        private readonly ClockInterface $clock,
    ) {
    }

    public function generate(string $type): Response
    {
        $session = $this->getSession();

        $challenge = $this->registry->challenge($type);
        $generator = $this->registry->generator($type);

        $key = $challenge->generateKey();

        $this->clearVerificationState($session);

        $session->set(self::SESSION_CURRENT_KEY, $key);
        $session->set(self::SESSION_TYPE, $type);
        $session->set(self::SESSION_GENERATED_AT, $this->now());

        return $generator->generate($key);
    }

    public function verify(
        ?string $type,
        string $answer,
        ?string $challenge = null,
    ): bool {
        $session = $this->getSession();

        $this->guardLocked($session);

        if (!$this->isSolveTimeValid($session)) {
            $this->registerFailure($session);

            return false;
        }

        $captchaType = $this->resolveCaptchaType($session, $type);
        if (null === $captchaType) {
            return false;
        }

        $key = $this->getKey();
        if ('' === $key) {
            return false;
        }

        if (!$this->isChallengeMatching($key, $challenge)) {
            return false;
        }

        $challengeService = $this->registry->challenge($captchaType);
        $valid = $challengeService->verify($key, $answer);

        if ($valid) {
            $this->markAsVerified($session, $captchaType);

            return true;
        }

        $this->registerFailure($session);

        return false;
    }

    public function isVerified(?string $type): bool
    {
        $session = $this->getSession();

        $verified = $session->get(self::SESSION_VERIFIED, false);
        $verifiedType = $session->get(self::SESSION_VERIFIED_TYPE);

        if (true !== $verified) {
            return false;
        }

        if (null !== $type && $verifiedType !== $type) {
            return false;
        }

        return true;
    }

    public function consumeVerified(?string $type): bool
    {
        if (!$this->isVerified($type)) {
            return false;
        }

        $session = $this->getSession();

        $this->clearVerificationState($session);
        $this->resetCaptchaState($session);

        return true;
    }

    public function getKey(): string
    {
        $session = $this->getSession();
        $key = $session->get(self::SESSION_CURRENT_KEY);

        return is_string($key) ? $key : '';
    }

    private function guardLocked(SessionInterface $session): void
    {
        $lockedUntil = (int) $session->get(self::SESSION_LOCKED_UNTIL, 0);

        if ($lockedUntil > $this->now()) {
            throw new CaptchaLockedException();
        }

        if ($lockedUntil > 0 && $lockedUntil <= $this->now()) {
            $session->remove(self::SESSION_LOCKED_UNTIL);
            $session->set(self::SESSION_TRIES, 0);
        }
    }

    private function resolveCaptchaType(SessionInterface $session, ?string $type): ?string
    {
        $sessionType = $session->get(self::SESSION_TYPE);

        if (!is_string($sessionType) || '' === $sessionType) {
            return null;
        }

        if (null !== $type && $type !== $sessionType) {
            return null;
        }

        return $sessionType;
    }

    private function isChallengeMatching(string $key, ?string $challenge): bool
    {
        if (null === $challenge) {
            return true;
        }

        return hash_equals($key, $challenge);
    }

    private function registerFailure(SessionInterface $session): void
    {
        $tries = (int) $session->get(self::SESSION_TRIES, 0);
        ++$tries;

        $session->set(self::SESSION_TRIES, $tries);

        if ($tries >= self::LIMIT_TRIES) {
            $session->set(
                self::SESSION_LOCKED_UNTIL,
                $this->now() + self::LOCK_SECONDS
            );

            $this->resetCaptchaState($session);

            throw new CaptchaLockedException();
        }
    }

    private function markAsVerified(SessionInterface $session, string $captchaType): void
    {
        $session->set(self::SESSION_TRIES, 0);
        $session->remove(self::SESSION_LOCKED_UNTIL);
        $session->set(self::SESSION_VERIFIED, true);
        $session->set(self::SESSION_VERIFIED_TYPE, $captchaType);
    }

    private function clearVerificationState(SessionInterface $session): void
    {
        $session->remove(self::SESSION_VERIFIED);
        $session->remove(self::SESSION_VERIFIED_TYPE);
    }

    private function resetCaptchaState(SessionInterface $session): void
    {
        $session->remove(self::SESSION_CURRENT_KEY);
        $session->remove(self::SESSION_GENERATED_AT);
        $session->remove(self::SESSION_TYPE);
        $session->remove(self::SESSION_LOCKED_UNTIL);
        $session->set(self::SESSION_TRIES, 0);
    }

    private function isSolveTimeValid(SessionInterface $session): bool
    {
        $generatedAt = (int) $session->get(self::SESSION_GENERATED_AT, 0);

        if ($generatedAt <= 0) {
            return false;
        }

        $elapsed = $this->now() - $generatedAt;

        if ($elapsed < self::MIN_SOLVE_SECONDS) {
            return false;
        }

        if ($elapsed > self::MAX_SOLVE_SECONDS) {
            return false;
        }

        return true;
    }

    private function now(): int
    {
        return $this->clock->now()->getTimestamp();
    }

    private function getSession(): SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new CaptchaRuntimeException('CaptchaService requires a current Request.');
        }

        return $request->getSession();
    }
}
