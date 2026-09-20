<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /** @return Project[] */
    public function findVisibleForDeveloper(User $developer): array
    {
        return $this->createQueryBuilder('project')
            ->andWhere(':developer MEMBER OF project.assignedDevelopers')
            ->setParameter('developer', $developer)
            ->orderBy('project.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
