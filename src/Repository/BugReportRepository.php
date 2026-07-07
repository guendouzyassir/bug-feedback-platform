<?php

namespace App\Repository;

use App\Entity\BugReport;
use App\Entity\User;
use App\Enum\BugPriority;
use App\Enum\BugStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BugReport>
 */
class BugReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BugReport::class);
    }

    /**
     * @param array{
     *     keyword?: string|null,
     *     project?: int|null,
     *     status?: BugStatus|null,
     *     priority?: BugPriority|null,
     *     developer?: int|null,
     *     dateFrom?: \DateTimeImmutable|null,
     *     dateTo?: \DateTimeImmutable|null
     * } $filters
     *
     * @return BugReport[]
     */
    public function findVisibleWithFilters(User $user, bool $canSeeAllBugs, array $filters): array
    {
        $queryBuilder = $this->createQueryBuilder('bug')
            ->leftJoin('bug.project', 'project')
            ->addSelect('project')
            ->leftJoin('bug.reporter', 'reporter')
            ->addSelect('reporter')
            ->leftJoin('bug.assignedDeveloper', 'developer')
            ->addSelect('developer')
            ->orderBy('bug.createdAt', 'DESC');

        if (!$canSeeAllBugs) {
            $queryBuilder
                ->andWhere('bug.reporter = :currentUser')
                ->setParameter('currentUser', $user);
        }

        if (!empty($filters['keyword'])) {
            $queryBuilder
                ->andWhere('(LOWER(bug.title) LIKE :keyword OR LOWER(bug.description) LIKE :keyword)')
                ->setParameter('keyword', '%'.mb_strtolower($filters['keyword']).'%');
        }

        if (!empty($filters['project'])) {
            $queryBuilder
                ->andWhere('project.id = :projectId')
                ->setParameter('projectId', $filters['project']);
        }

        if (!empty($filters['status'])) {
            $queryBuilder
                ->andWhere('bug.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $queryBuilder
                ->andWhere('bug.priority = :priority')
                ->setParameter('priority', $filters['priority']);
        }

        if (!empty($filters['developer'])) {
            $queryBuilder
                ->andWhere('developer.id = :developerId')
                ->setParameter('developerId', $filters['developer']);
        }

        if (!empty($filters['dateFrom'])) {
            $queryBuilder
                ->andWhere('bug.createdAt >= :dateFrom')
                ->setParameter('dateFrom', $filters['dateFrom']->setTime(0, 0));
        }

        if (!empty($filters['dateTo'])) {
            $queryBuilder
                ->andWhere('bug.createdAt <= :dateTo')
                ->setParameter('dateTo', $filters['dateTo']->setTime(23, 59, 59));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('bug')
            ->select('bug.status AS status, COUNT(bug.id) AS bugCount')
            ->groupBy('bug.status')
            ->orderBy('bug.status', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $status = match (true) {
                $row['status'] instanceof BugStatus => $row['status']->value,
                is_object($row['status']) && isset($row['status']->value) => (string) $row['status']->value,
                default => (string) $row['status'],
            };
            $counts[$status] = (int) $row['bugCount'];
        }

        return $counts;
    }

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function countByProject(): array
    {
        $rows = $this->createQueryBuilder('bug')
            ->select('project.name AS name, COUNT(bug.id) AS bugCount')
            ->innerJoin('bug.project', 'project')
            ->groupBy('project.id')
            ->addGroupBy('project.name')
            ->orderBy('bugCount', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row): array => [
                'name' => (string) $row['name'],
                'count' => (int) $row['bugCount'],
            ],
            $rows
        );
    }
}
