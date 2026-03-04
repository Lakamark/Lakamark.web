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

    public function findOneByTokenHashAndType(string $tokenHash, TokenRequestType $type): ?TokenRequest
    {
        return $this->createQueryBuilder('tr')
            ->andWhere('tr.tokenHash = :hash')
            ->andWhere('tr.type = :type')
            ->setParameter('hash', $tokenHash)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function revokeActiveForUserAndType(int $userId, TokenRequestType $type, \DateTimeImmutable $now): int
    {
        // mark all active (not consumed + not expired) as consumed now
        return $this->getEntityManager()->createQueryBuilder()
            ->update(TokenRequest::class, 'tr')
            ->set('tr.consumedAt', ':now')
            ->where('tr.user = :userId')
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
