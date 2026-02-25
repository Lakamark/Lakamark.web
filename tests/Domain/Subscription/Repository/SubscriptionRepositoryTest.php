<?php

namespace App\Tests\Domain\Subscription\Repository;

use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Enum\SubscriptionProvider;
use App\Domain\Subscription\Enum\SubscriptionStatus;
use App\Domain\Subscription\Repository\SubscriptionRepository;
use App\Tests\RepositoryTestCase;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * @extends RepositoryTestCase<SubscriptionRepository>
 */
class SubscriptionRepositoryTest extends RepositoryTestCase
{
    protected string $repositoryClass = SubscriptionRepository::class;

    public function testFindOneByUserIdReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->findOneByUserId(12);
        $this->assertNull($result);
    }

    /**
     * @throws ORMException
     */
    public function testFindOneByUserIdReturnsSubscription(): void
    {
        $sub = $this->makeSubscription(
            userId: 13,
            status: SubscriptionStatus::ACTIVE,
            periodEnd: new \DateTimeImmutable('+10 days'),
        );

        $this->em->persist($sub);
        $this->em->flush();
        $this->em->clear();

        /** @var Subscription $found */
        $found = $this->repository->findOneByUserId(13);

        $this->assertNotNull($found);
        $this->assertSame(13, $found->getUserId());
    }

    /**
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    public function testHasActiveSubscriptionTrueWhenActiveAndNotExpired(): void
    {
        $now = new \DateTimeImmutable('2026-02-25 12:00:00');

        $sub = $this->makeSubscription(
            userId: 14,
            status: SubscriptionStatus::ACTIVE,
            periodEnd: $now->modify('+1 day'),
        );

        $this->em->persist($sub);
        $this->em->flush();
        $this->em->clear();

        $this->assertTrue($this->repository->hasActiveSubscription(14, $now));
    }

    /**
     * @throws \DateMalformedStringException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testHasActiveSubscriptionFalseWhenExpired(): void
    {
        $now = new \DateTimeImmutable('2026-02-25 12:00:00');

        $sub = $this->makeSubscription(
            userId: 15,
            status: SubscriptionStatus::ACTIVE,
            periodEnd: $now->modify('-1 minute'),
        );

        $this->em->persist($sub);
        $this->em->flush();
        $this->em->clear();

        $this->assertFalse($this->repository->hasActiveSubscription(15, $now));
    }

    /**
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    public function testHasActiveSubscriptionFalseWhenCanceled(): void
    {
        $now = new \DateTimeImmutable('2026-02-25 12:00:00');

        $sub = $this->makeSubscription(
            userId: 16,
            status: SubscriptionStatus::CANCELED,
            periodEnd: $now->modify('+10 days'),
            canceledAt: $now->modify('-1 hour')
        );

        $this->em->persist($sub);
        $this->em->flush();
        $this->em->clear();

        $this->assertFalse($this->repository->hasActiveSubscription(16, $now));
    }

    private function makeSubscription(
        int $userId,
        SubscriptionStatus $status,
        \DateTimeImmutable $periodEnd,
        ?\DateTimeImmutable $canceledAt = null,
    ): Subscription {
        $now = new \DateTimeImmutable('2026-02-25 12:00:00');

        return (new Subscription())
            ->setUserId($userId)
            ->setStatus($status)
            ->setProvider(SubscriptionProvider::MANUAL)
            ->setProviderRef(null)
            ->setStartedAt($now)
            ->setCurrentPeriodEnd($periodEnd)
            ->setCanceledAt($canceledAt)
            ->setUpdatedAt($now);
    }
}
