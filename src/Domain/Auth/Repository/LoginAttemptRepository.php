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

    /**
     * @throws \DateMalformedStringException
     */
    public function countRecentAttemptsFor(User $user, int $minutes, \DateTimeImmutable $now): int
    {
        $cutoff = $now->modify("-{$minutes} minutes");

        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.user = :user')
            ->andWhere('a.createdAt >= :cutoff')
            ->setParameter('user', $user)
            ->setParameter('cutoff', $cutoff)
            ->getQuery()
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
