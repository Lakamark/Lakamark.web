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
use Symfony\Component\VarDumper\Cloner\Stub;

class AuthenticatorTest extends TestCase
{
    private UserRepository|MockObject $userRepository;

    private Stub|EventDispatcherInterface $dispatcher;

    private Authenticator $authenticator;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock the userRepository
        $this->userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock URL Generator Class
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);

        // Mock URL Matcher Class
        $this->createStub(UrlMatcherInterface::class)
            ->method('match')
            ->willReturn([]);

        // Init the authenticator
        $this->authenticator = new Authenticator(
            $this->userRepository,
            $urlGenerator,
            $this->dispatcher,
            $this->createStub(UrlMatcherInterface::class)
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
            ->method('findByUsernameIdentifier')
            ->with('johndo@lakamark.com')
            ->willReturn(new User());

        // Generate the passport credential
        $passport = $this->authenticator->authenticate($request);
        $this->assertEquals($passport->getUser(), $user);
        $this->assertTrue($passport->hasBadge(CsrfTokenBadge::class));
        $this->assertTrue($passport->hasBadge(PasswordCredentials::class));
    }
}
