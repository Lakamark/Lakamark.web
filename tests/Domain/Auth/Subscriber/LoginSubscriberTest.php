<?php

namespace App\Tests\Domain\Auth\Subscriber;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\BadPasswordLoginEvent;
use App\Domain\Auth\Service\LoginAttemptsService;
use App\Domain\Auth\Subscriber\LoginSubscriber;
use App\Tests\TestCases\EventTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class LoginSubscriberTest extends EventTestCase
{
    private MockObject|LoginAttemptsService $service;

    public function testLoginBadPasswordAttempt(): void
    {
        // Create a mocked subscriber
        $subscriber = $this->getMockedSubscriber();
        $event = $this->getMockedEvent();

        $this->service->expects($this->once())
            ->method('incrementAttempt')
            ->with($event->getUser());

        $this->dispatch($subscriber, $event);
    }

    private function getMockedSubscriber(): LoginSubscriber
    {
        /* @var MockObject|LoginAttemptsService $service */
        $this->service = $this->createMock(LoginAttemptsService::class);
        $em = $this->createStub(EntityManagerInterface::class);

        return new LoginSubscriber($this->service, $em);
    }

    private function getMockedEvent(): BadPasswordLoginEvent
    {
        $user = new User();

        return new BadPasswordLoginEvent($user);
    }
}
