<?php

namespace App\Domain\Badge\Repository;

use App\Domain\Badge\Entity\BadgeUnlock;
use App\Foundation\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<BadgeUnlock>
 */
class BadgeUnlockRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BadgeUnlock::class);
    }

}
