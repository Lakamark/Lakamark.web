<?php

namespace App\Tests\Http\Dashboard\Firewall;

use App\Http\Dashboard\Firewall\DashboardRequestListener;
use App\Tests\Http\Dashboard\Controller\Stubs\DummyDashboardController;
use App\Tests\Http\Dashboard\Controller\Stubs\DummyDashboardInvokableController;
use App\Tests\KernelTestCase;
use App\Tests\Stubs\Http\Controller\DummyOtherController;
use App\Tests\Stubs\Http\Controller\DummyOtherInvokableController;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DashboardRequestListenerTest extends KernelTestCase
{
    public static function controllerDataProvider(): \Generator
    {
        // controllerCallable, shouldCheck, isGranted, expectException
        yield 'dashboard array denied' => [[new DummyDashboardController(), 'index'], true, false, true];
        yield 'dashboard array allowed' => [[new DummyDashboardController(), 'index'], true, true, false];

        yield 'dashboard invokable denied' => [new DummyDashboardInvokableController(), true, false, true];
        yield 'dashboard invokable allowed' => [new DummyDashboardInvokableController(), true, true, false];

        yield 'other array' => [[new DummyOtherController(), 'index'], false, false, false];
        yield 'other invokable' => [new DummyOtherInvokableController(), false, false, false];
    }

    #[DataProvider('controllerDataProvider')]
    public function testOnController(
        mixed $controllerCallable,
        bool $shouldCheck,
        bool $isGranted,
        bool $expectException,
    ): void {
        self::bootKernel();
        $kernel = self::$kernel;

        $request = Request::create('/anything');
        $event = new ControllerEvent(
            $kernel,
            $controllerCallable,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->expects($this->exactly($shouldCheck ? 1 : 0))
            ->method('isGranted')
            ->with('CMS_MANAGE')
            ->willReturn($isGranted);

        $listener = new DashboardRequestListener($authChecker);

        if ($expectException) {
            $this->expectException(AccessDeniedException::class);
        }

        $listener->onController($event);

        if (!$expectException) {
            $this->addToAssertionCount(1);
        }
    }

    public function testIgnoreSubRequest(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        $request = Request::create('/anything');
        $event = new ControllerEvent(
            $kernel,
            [new DummyDashboardController(), 'index'],
            $request,
            HttpKernelInterface::SUB_REQUEST
        );

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->expects($this->never())->method('isGranted');

        $listener = new DashboardRequestListener($authChecker);
        $listener->onController($event);

        $this->addToAssertionCount(1);
    }
}
