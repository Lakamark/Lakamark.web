<?php

namespace App\Domain\Auth\Repository;

use App\Domain\Auth\Contract\TokenRequestRepositoryInterface;
use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
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

    public function save(TokenRequest $tokenRequest, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->persist($tokenRequest);

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

    public function findUsableForUserAndType(
        User $user,
        TokenRequestType $type,
        \DateTimeImmutable $now,
    ): array {
        return $this->createQueryBuilder('tr')
            ->andWhere('tr.user = :user')
            ->andWhere('tr.type = :type')
            ->andWhere('tr.consumedAt IS NULL')
            ->andWhere('tr.revokedAt IS NULL')
            ->andWhere('tr.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }
}
