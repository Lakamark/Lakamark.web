<?php

namespace App\Domain\Auth\Repository;

use App\Domain\Auth\Entity\User;
use App\Foundation\Orm\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends AbstractRepository<User>
 */
class UserRepository extends AbstractRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findUserForAuth(string $username): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.email) = :username')
            ->orWhere('LOWER(u.username) = :username')
            ->setMaxResults(1)
            ->setParameter('username', mb_strtolower($username))
            ->getQuery()
            ->getOneOrNullResult();
    }

}
