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
    private UserRepository|MockObject $userRepository;

    private Authenticator $authenticator;

    // TODO They are Mock issues notices to fix. The test successful pass.
    /*
     * Notice message:
     * App\Tests\Domain\Auth\AuthenticatorTest::testAuthenticateHasRightParameters
     * * No expectations were configured for the mock object for Symfony\Component\Routing\Generator\UrlGeneratorInterface.
     * Consider refactoring your test code to use a test stub instead.
     * The #[AllowMockObjectsWithoutExpectations] attribute can be used to opt out of this check.
     *
     * We should Mock this object:
     * Symfony\Component\Routing\Generator\UrlGeneratorInterface.
     * Psr\EventDispatcher\EventDispatcherInterface.
     * Symfony\Component\Routing\Matcher\UrlMatcherInterface.
     * Symfony\Component\Routing\Matcher\UrlMatcherInterface.
     */
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
            $this->createMock(UrlMatcherInterface::class)
        );
    }

    public function testAuthenticateHasRightParameters(): void
    {
        // Prepare the request and set a fake session
        $request = new Request([], ['_username' => 'johndo@lakamark.com']);
        $request->setSession(new Session(new MockArraySessionStorage()));

        // Prepare the SQL request to find a user in the DB
        $user = new User();
        $this->userRepository
            ->expects($this->once())
            ->method('findByUsernameForAuth')
            ->with('johndo@lakamark.com')
            ->willReturn(new User());

        // Generate the passport credential
        $passport = $this->authenticator->authenticate($request);
        $this->assertEquals($passport->getUser(), $user);
        $this->assertTrue($passport->hasBadge(CsrfTokenBadge::class));
        $this->assertTrue($passport->hasBadge(PasswordCredentials::class));
    }
}
