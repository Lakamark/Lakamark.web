<?php

namespace App\Foundation\Captcha;

use App\Foundation\Captcha\Contract\CaptchaRegistryInterface;
use App\Foundation\Captcha\Contract\CaptchaVerifierInterface;
use App\Foundation\Captcha\Exception\CaptchaLockedException;
use App\Foundation\Captcha\Exception\CaptchaRunTimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class CaptchaService implements CaptchaVerifierInterface
{
    private const int LIMIT_TRIES = 3;
    private const string SESSION_CURRENT_KEY = 'CAPTCHA_KEY';
    private const string CAPTCHA_SESSION_TYPE = 'CAPTCHA_TYPE';
    private const string SESSION_TRIES = 'CAPTCHA_TRIES';

    public function __construct(
        private readonly CaptchaRegistryInterface $registry,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function generate(string $type): Response
    {
        $session = $this->getSession();

        $challenge = $this->registry->challenge($type);
        $generator = $this->registry->generator($type);

        $key = $challenge->generateKey();

        // Store in the session
        $session->set(self::SESSION_CURRENT_KEY, $key);
        $session->set(self::CAPTCHA_SESSION_TYPE, $type);
        $session->set(self::SESSION_TRIES, 0);

        return $generator->generate($key);
    }

    public function verify(?string $type, string $answer, ?string $challenge = null): bool
    {
        $session = $this->getSession();

        $sessionType = $session->get(self::CAPTCHA_SESSION_TYPE);
        if (!is_string($sessionType) || '' === $sessionType) {
            return false;
        }

        // Strict type if the constraint define a type,
        // Should to match with the session
        if (null !== $type && $type !== $sessionType) {
            return false;
        }

        $type = $sessionType;

        $key = $this->getKey();
        if ('' === $key) {
            return false;
        }

        // If the submit form a challenge key,
        // It should to match with the current session.
        if (null !== $challenge && !hash_equals($key, $challenge)) {
            return false;
        }

        // If the user extends maximum allowed tries.
        $tries = (int) $session->get(self::SESSION_TRIES, 0);
        if ($tries >= self::LIMIT_TRIES) {
            throw new CaptchaLockedException();
        }

        $challengeService = $this->registry->challenge($type);

        // Valid the captcha
        $valid = $challengeService->verify($key, $answer);
        if ($valid) {
            $session->set(self::SESSION_TRIES, 0);

            // To remove the challenge from the session (anti-replay)
            $session->remove(self::SESSION_CURRENT_KEY);

            return true;
        }

        $session->set(self::SESSION_TRIES, $tries + 1);

        return false;
    }

    public function getKey(): string
    {
        $session = $this->getSession();

        $key = $session->get(self::SESSION_CURRENT_KEY);

        return is_string($key) ? $key : '';
    }

    private function getSession(): SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            throw new CaptchaRunTimeException('CaptchaService requires a current Request.');
        }

        return $request->getSession();
    }
}
