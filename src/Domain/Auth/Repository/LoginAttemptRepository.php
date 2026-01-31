<?php

namespace App\Domain\Auth\Repository;

use App\Domain\Auth\Entity\LoginAttempt;
use App\Domain\Auth\Entity\User;
use App\Foundation\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<LoginAttempt>
 */
class LoginAttemptRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginAttempt::class);
    }

    public function countRecentAttemptsFor(User $user, int $minutes): int
    {
        $cutoff = new \DateTimeImmutable("-{$minutes} minutes");

        return $this->createQueryBuilder('la')
            ->select('COUNT(la.id) as attempt')
            ->where('la.user = :user')
            ->andWhere('la.createdAt >= :date')
            ->setParameter('date', $cutoff)
            ->setParameter('user', $user)
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleScalarResult();
    }

    public function resetAttemptsFor(User $user): int
    {
        return $this->createQueryBuilder('la')
            ->delete()
            ->where('la.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
