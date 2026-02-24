<?php

namespace App\Tests\Http\Dashboard\Firewall;

use App\Http\Dashboard\Firewall\DashboardRequestListener;
use App\Tests\KernelTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DashboardRequestListenerTest extends KernelTestCase
{
    public static function urlDataProvider(): \Generator
    {
        yield ['/dashboard', '/dashboard/posts', true];
        yield ['dashboard', '/dashboard/posts', true];
        yield ['/dashboard', '/dashboard/', true];
        yield ['/dashboard', '/dashboard', true];
        yield ['/dashboard', '/fake-dashboard', false];
        yield ['/dashboard', '/fakedashboard/posts', false];
        yield ['/dashboard', '/dashboard/posts', false, true];
    }

    #[DataProvider('urlDataProvider')]
    public function testPosts(string $prefix, string $uri, bool $expectException, bool $isGranted = false): void
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('isMainRequest')
            ->willReturn(true);

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->once())
            ->method('getRequestUri')
            ->willReturn($uri);

        $normalizedUri = '/'.trim($uri, '/').'/';
        $normalizedPrefix = '/'.trim($prefix, '/').'/';
        $matchesPrefix = substr($normalizedUri, 0, mb_strlen($normalizedPrefix)) === $normalizedPrefix;

        $willThrow = $matchesPrefix && false === $isGranted;

        $event->expects($this->exactly($willThrow ? 2 : 1))
            ->method('getRequest')
            ->willReturn($request);

        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)
            ->getMock();

        $authChecker->expects($this->exactly($matchesPrefix ? 1 : 0))
            ->method('isGranted')
            ->with('CMS_MANAGE')
            ->willReturn($isGranted);

        $listener = new DashboardRequestListener($prefix, $authChecker);

        if ($expectException) {
            $this->expectException(AccessDeniedException::class);
        } else {
            $this->addToAssertionCount(1);
        }

        $listener->onRequest($event);
    }
}
