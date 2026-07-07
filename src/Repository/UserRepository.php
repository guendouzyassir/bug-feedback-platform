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
        $developers = array_filter(
            $this->findBy(['isActive' => true]),
            static fn (User $user): bool => in_array('ROLE_DEVELOPER', $user->getRoles(), true)
        );

        usort($developers, static fn (User $a, User $b): int => strcmp((string) $a->getFullName(), (string) $b->getFullName()));

        return array_values($developers);
    }
}
