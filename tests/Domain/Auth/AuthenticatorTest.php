<?php

namespace App\Tests\Domain\Auth;

use App\Domain\Auth\Authenticator;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class AuthenticatorTest extends TestCase
{
    private MockObject|UserRepository $userRepository;

    private Authenticator $authenticator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlGenerator = $this->getMockBuilder(UrlGeneratorInterface::class)
            ->getMock();
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $urlMatcher = $this->createMock(UrlMatcherInterface::class);
        $urlMatcher->expects($this->any())->method('match')->willReturn([]);
        $this->authenticator = new Authenticator(
            $this->userRepository,
            $urlGenerator,
            $eventDispatcher,
            $this->createMock(UrlMatcherInterface::class),
        );
    }

    public function testPassportAuthenticateHasRightParameters(): void
    {
        $request = new Request([], ['_username' => 'dummy@dummy.com']);
        $request->setSession(new Session(new MockArraySessionStorage()));

        $user = new User();
        $this->userRepository
            ->expects($this->once())
            ->method('findUserForAuth')
            ->with('dummy@dummy.com')
            ->willReturn(new User());

        $passport = $this->authenticator->authenticate($request);
        $this->assertEquals($passport->getUser(), $user);
        $this->assertTrue($passport->hasBadge(CsrfTokenBadge::class));
        $this->assertTrue($passport->hasBadge(PasswordCredentials::class));
    }
}
