<?php

namespace App\Tests\Domain\Auth\Service;

use App\Domain\Auth\Entity\AuthAttempt;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\AuthAttemptRepository;
use App\Domain\Auth\Service\LoginAttemptsService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoginAttemptsServiceTest extends TestCase
{
    public function testItShouldCreateAttempt(): void
    {
        /** @var MockObject|EntityManagerInterface $em */
        $em = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();

        /** @var MockObject|AuthAttemptRepository $repository */
        $repository = $this->getStubBuilder(AuthAttemptRepository::class)
            ->disableOriginalConstructor()
            ->getStub();

        $service = new LoginAttemptsService($repository, $em);
        $user = new User();

        $em->expects($this->once())->method('persist')->with(
            $this->callback(fn (AuthAttempt $attempt) => $attempt->getUser() === $user)
        );
        $em->expects($this->once())->method('flush');

        $service->incrementAttempt($user);
    }
}
