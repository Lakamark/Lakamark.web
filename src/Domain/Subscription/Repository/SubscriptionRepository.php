<?php

namespace App\Domain\Subscription\Repository;

use App\Domain\Subscription\Entity\Subscription;
use App\Domain\Subscription\Enum\SubscriptionStatus;
use App\Foundation\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Subscription>
 */
class SubscriptionRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    public function findOneByUserId(int $userId): ?Subscription
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function hasActiveSubscription(int $userId, \DateTimeImmutable $now): bool
    {
        $count = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.userId = :userId')
            ->andWhere('s.status = :status')
            ->andWhere('s.currentPeriodEnd > :now')
            ->setParameter('userId', $userId)
            ->setParameter('status', SubscriptionStatus::ACTIVE)
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function save(Subscription $subscription): void
    {
        $this->getEntityManager()->persist($subscription);
        $this->getEntityManager()->flush();
    }
}
