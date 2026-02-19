<?php

namespace App\Foundation\Captcha;

use App\Foundation\Captcha\Contract\CaptchaChallengeInterface;
use App\Foundation\Captcha\Contract\CaptchaGeneratorInterface;
use App\Foundation\Captcha\Contract\CaptchaRegistryInterface;
use App\Foundation\Captcha\Exception\CaptchaInvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final readonly class CaptchaRegistry implements CaptchaRegistryInterface
{
    public function __construct(
        private ContainerInterface $challenges,
        private ContainerInterface $generators,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function challenge(string $type): CaptchaChallengeInterface
    {
        if (!$this->challenges->has($type)) {
            throw new CaptchaInvalidArgumentException("Unknown captcha type: $type");
        }

        $svc = $this->challenges->get($type);

        if (!$svc instanceof CaptchaChallengeInterface) {
            throw new \LogicException(sprintf('Challenge "%s" must implement CaptchaChallengeInterface.', $type));
        }

        return $svc;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function generator(string $type): CaptchaGeneratorInterface
    {
        if (!$this->generators->has($type)) {
            throw new CaptchaInvalidArgumentException("Unknown captcha generator: $type");
        }

        $svc = $this->generators->get($type);

        if (!$svc instanceof CaptchaGeneratorInterface) {
            throw new \LogicException(sprintf('Generator "%s" must implement CaptchaGeneratorInterface.', $type));
        }

        return $svc;
    }
}
