<?php

namespace App\Domain\Moderation\Repository;

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
}
