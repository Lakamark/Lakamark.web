<?php

namespace App\Tests\Foundation\Captcha;

use App\Foundation\Captcha\CaptchaRegistry;
use App\Foundation\Captcha\Contract\CaptchaChallengeInterface;
use App\Foundation\Captcha\Contract\CaptchaGeneratorInterface;
use App\Foundation\Captcha\Exception\CaptchaInvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class CaptchaRegistryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsChallengeForKnownType(): void
    {
        $type = 'puzzle';

        // Challenges containers and generator containers
        $challenges = $this->createMock(ContainerInterface::class);
        $generators = $this->createMock(ContainerInterface::class);

        $challenge = $this->createStub(CaptchaChallengeInterface::class);

        $challenges
            ->expects($this->once())
            ->method('has')
            ->with($type)
            ->willReturn(true);

        $challenges
            ->expects($this->once())
            ->method('get')
            ->with($type)
            ->willReturn($challenge);

        // Don't call container generators in this test.
        $generators->expects($this->never())->method('has');
        $generators->expects($this->never())->method('get');

        $registry = new CaptchaRegistry(
            $challenges,
            $generators,
        );

        $this->assertSame($challenge, $registry->challenge($type));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReturnsGeneratorForKnownType(): void
    {
        $type = 'puzzle';

        // Challenges containers and generator containers
        $challenges = $this->createMock(ContainerInterface::class);
        $generators = $this->createMock(ContainerInterface::class);

        $generator = $this->createStub(CaptchaGeneratorInterface::class);

        $generators->expects($this->once())
            ->method('has')
            ->with($type)
            ->willReturn(true);

        $generators->expects($this->once())
            ->method('get')
            ->with($type)
            ->willReturn($generator);

        // Ensure the challenges container are not called in this test case.
        $challenges->expects($this->never())->method('has');
        $challenges->expects($this->never())->method('get');

        $registry = new CaptchaRegistry(
            $challenges,
            $generators,
        );

        $this->assertSame($generator, $registry->generator($type));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testChallengeThrowsExceptionForUnknownType(): void
    {
        $type = 'unknown';

        // Challenges containers and generator containers
        $challenges = $this->createMock(ContainerInterface::class);
        $generators = $this->createMock(ContainerInterface::class);

        $challenges
            ->expects($this->once())
            ->method('has')
            ->with($type)
            ->willReturn(false);

        // Shouldn't call get if it has method return false.
        $challenges->expects($this->never())->method('get');

        // Generator containers won't change.
        $generators->expects($this->never())->method('has');
        $generators->expects($this->never())->method('get');

        // Call the register.
        $registry = new CaptchaRegistry(
            $challenges,
            $generators,
        );

        $this->expectException(CaptchaInvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown captcha type: unknown');

        $registry->challenge($type);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGeneratorThrowsExceptionForUnknownType(): void
    {
        $type = 'unknown';

        // Challenges containers and generator containers
        $challenges = $this->createMock(ContainerInterface::class);
        $generators = $this->createMock(ContainerInterface::class);

        $generators->expects($this->once())
            ->method('has')
            ->with($type)
            ->willReturn(false);

        // Shouldn't call challenges container.
        $challenges->expects($this->never())->method('has');
        $challenges->expects($this->never())->method('get');

        // shouldn't call the get method on generators containers.
        $generators->expects($this->never())->method('get');

        // Call the register.
        $registry = new CaptchaRegistry(
            $challenges,
            $generators,
        );

        $this->expectException(CaptchaInvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown captcha generator: unknown');

        $registry->generator($type);
    }
}
