<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Project;
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
        return $this->createQueryBuilder('u')
            ->where('u.isActive = :active')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('active', true)
            ->setParameter('role', '%ROLE_DEVELOPER%')
            ->orderBy('u.fullName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return User[]
     */
    public function findDevelopersForProject(?Project $project): array
    {
        if ($project === null) {
            return [];
        }

        return $this->createQueryBuilder('u')
            ->where('u.isActive = :active')
            ->andWhere('u.roles LIKE :role')
            ->andWhere(':project MEMBER OF u.developmentProjects')
            ->setParameter('active', true)
            ->setParameter('role', '%ROLE_DEVELOPER%')
            ->setParameter('project', $project)
            ->orderBy('u.fullName', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return User[] */
    public function findByRole(?string $role): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC');

        if ($role !== null && $role !== '') {
            $qb
                ->where('u.roles LIKE :role')
                ->setParameter('role', '%' . $role . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
