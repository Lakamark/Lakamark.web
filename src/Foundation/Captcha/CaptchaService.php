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

    public function verify(?string $type, string $answer): bool
    {
        $session = $this->getSession();
        $type ??= $session->get(self::CAPTCHA_SESSION_TYPE);

        if (!is_string($type) || '' === $type) {
            return false;
        }

        $key = $session->get(self::SESSION_CURRENT_KEY);

        if (!is_string($key) || '' === $key) {
            return false;
        }

        // If the user extend the max tries we return an exception.
        $tries = (int) $session->get(self::SESSION_TRIES, 0);
        if ($tries >= self::LIMIT_TRIES) {
            throw new CaptchaLockedException();
        }

        $challenge = $this->registry->challenge($type);

        // If the user answer match with the expected solution.
        // Reset the count index tries in the session.
        $valid = $challenge->verify($key, $answer);
        if ($valid) {
            $session->set(self::SESSION_TRIES, 0);

            return true;
        }

        // Increment the tries index
        $session->set(self::SESSION_TRIES, $tries + 1);

        return false;
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
