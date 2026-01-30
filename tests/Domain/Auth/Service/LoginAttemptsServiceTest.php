<?php

namespace App\Tests\Domain\Auth\Service;

use App\Domain\Auth\Entity\LoginAttempt;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\LoginAttemptRepository;
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

        /** @var MockObject|LoginAttemptRepository $repository */
        $repository = $this->getStubBuilder(LoginAttemptRepository::class)
            ->disableOriginalConstructor()
            ->getStub();

        $service = new LoginAttemptsService($repository, $em);
        $user = new User();

        $em->expects($this->once())->method('persist')->with(
            $this->callback(fn (LoginAttempt $attempt) => $attempt->getUser() === $user)
        );
        $em->expects($this->once())->method('flush');

        $service->increment($user);
    }
}
