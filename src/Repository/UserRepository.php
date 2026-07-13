<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[]
     */
    public function findDevelopers(): array
    {
        return array_values(array_filter(
            $this->findBy(['isActive' => true], ['fullName' => 'ASC']),
            static fn (User $user): bool => in_array('ROLE_DEVELOPER', $user->getRoles(), true),
        ));
    }
}
