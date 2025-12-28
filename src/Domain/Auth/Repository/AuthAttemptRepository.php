<?php

namespace App\Domain\Auth\Repository;

use App\Domain\Auth\Entity\AuthAttempt;
use App\Domain\Auth\Entity\User;
use App\Foundation\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<AuthAttempt>
 */
class AuthAttemptRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthAttempt::class);
    }

    public function incrementAttemptsFor(User $user, int $minutes): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id) as count')
            ->where('a.user = :user')
            ->andWhere('a.createdAt = :date')
            ->setParameter('date', new \DateTimeImmutable("-{$minutes} minutes"))
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function restAttemptsFor(User $user): void
    {
        $this->createQueryBuilder('a')
        ->where('a.user = :user')
        ->setParameter('user', $user)
        ->delete()
        ->getQuery()
        ->execute();
    }
}
