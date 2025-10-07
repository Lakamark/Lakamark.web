<?php

namespace App\Tests\Http\Dashboard\Firewall;

use App\Http\Dashboard\Firewall\DashboardRequestEventListener;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DashboardRequestEventListenerTest extends KernelTestCase
{
    public static function uriDataProvider(): \Generator
    {
        yield ['/dashboard', '/dashboard/blog', true];
        yield ['dashboard', '/dashboard/blog', true];
        yield ['/dashboard', '/dashboard/', true];
        yield ['/dashboard', '/dashboard', true];
        yield ['/dashboard', '/dashboard-blog', false];
        yield ['/dashboard', '/xzxxdashboard/blog', false];
        yield ['/dashboard', '/dashboard/blog', false, true];
    }

    #[DataProvider('uriDataProvider')]
    public function testRequestListener(
        string $prefix,
        string $uri,
        bool $expectException,
        bool $isGranted = false,
    ): void {
        // Mock the RequestEvent class
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('isMainRequest')
            ->willReturn(true);

        // Mock the Request class
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->any())
            ->method('getRequestUri')
            ->willReturn($uri);
        $event->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);

        // Mock the authenticatorCheckerInterface
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $authChecker->expects($this->any())
            ->method('isGranted')
            ->willReturn($isGranted);

        $listener = new DashboardRequestEventListener($prefix, $authChecker);
        if (true === $expectException) {
            $this->expectException(AccessDeniedException::class);
        } else {
            $this->addToAssertionCount(1);
        }
        $listener->onRequest($event);
    }
}
