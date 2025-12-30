<?php

namespace App\Domain\Moderation\Repository;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;
use App\Foundation\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<UserBan>
 */
class UserBanRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBan::class);
    }

    /**
     * To find an active Ban in the table.
     */
    public function findActiveBanFor(User $user, \DateTimeImmutable $now): ?UserBan
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.user = :user')
            ->andWhere('b.endedAt IS NULL')
            ->andWhere('(b.expiresAt IS NULL OR b.expiresAt > :now)')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->orderBy('b.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find expired bands according to today date.
     */
    public function findExpiredNotEnded(\DateTimeImmutable $now): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.endedAt IS NULL')
            ->andWhere('b.expiresAt IS NOT NULL')
            ->andWhere('b.expiresAt <= :now')
            ->setParameter('now', $now)
            ->orderBy('b.expiresAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
