<?php

namespace App\Domain\Auth\Repository;

use App\Domain\Auth\Contract\TokenRequestRepositoryInterface;
use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Foundation\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<TokenRequest>
 */
class TokenRequestRepository extends AbstractRepository implements TokenRequestRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TokenRequest::class);
    }

    public function save(TokenRequest $request, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->persist($request);

        if ($flush) {
            $em->flush();
        }
    }

    public function findByTokenHashAndType(
        string $tokenHash,
        TokenRequestType $type,
    ): ?TokenRequest {
        return $this->createQueryBuilder('tr')
            ->addSelect('u')
            ->leftJoin('tr.user', 'u')
            ->andWhere('tr.tokenHash = :hash')
            ->andWhere('tr.type = :type')
            ->setParameter('hash', $tokenHash)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findConsumableByTokenHashAndType(
        string $tokenHash,
        TokenRequestType $type,
        \DateTimeImmutable $now,
    ): ?TokenRequest {
        return $this->createQueryBuilder('tr')
            ->addSelect('u')
            ->leftJoin('tr.user', 'u')
            ->andWhere('tr.tokenHash = :hash')
            ->andWhere('tr.type = :type')
            ->andWhere('tr.consumedAt IS NULL')
            ->andWhere('tr.expiresAt > :now')
            ->setParameter('hash', $tokenHash)
            ->setParameter('type', $type)
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Revoke all consumable tokens for a given user and token type.
     *
     * A consumable token is defined as:
     *  - not consumed
     *  - not expired
     *
     * This method is mainly used when issuing a new token
     * to ensure that only one active token exists per user/type.
     *
     * Returns the number of affected rows.
     */
    public function revokeConsumableForUserAndType(
        int $userId,
        TokenRequestType $type,
        \DateTimeImmutable $now,
    ): int {
        // mark all consumable tokens (not consumed + not expired) as consumed now
        return $this->createQueryBuilder('tr')
            ->update(TokenRequest::class, 'tr')
            ->set('tr.consumedAt', ':now')
            ->andWhere('IDENTITY(tr.user) = :userId')
            ->andWhere('tr.type = :type')
            ->andWhere('tr.consumedAt IS NULL')
            ->andWhere('tr.expiresAt > :now')
            ->setParameter('now', $now)
            ->setParameter('userId', $userId)
            ->setParameter('type', $type)
            ->getQuery()
            ->execute();
    }
}
