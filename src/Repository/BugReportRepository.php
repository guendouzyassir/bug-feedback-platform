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
    public function findVisibleWithFilters(User $user, array $filters): array
    {
        $queryBuilder = $this->createQueryBuilder('bug')
            ->leftJoin('bug.project', 'project')
            ->addSelect('project')
            ->leftJoin('bug.reporter', 'reporter')
            ->addSelect('reporter')
            ->leftJoin('bug.assignedDeveloper', 'developer')
            ->addSelect('developer')
            ->orderBy('bug.createdAt', 'DESC');

        if ($user->isAdmin()) {
            // Admin sees everything - no restriction
        } elseif ($user->isDeveloper()) {
            // Developer sees only bugs assigned to them
            $queryBuilder
                ->andWhere('bug.assignedDeveloper = :currentUser')
                ->setParameter('currentUser', $user);
        } elseif ($user->isClient()) {
            // Client sees only bugs in their assigned projects
            $projectIds = $user->getAssignedProjects()->map(fn ($p) => $p->getId())->toArray();

            if (empty($projectIds)) {
                $queryBuilder
                    ->andWhere('1 = 0');
            } else {
                $queryBuilder
                    ->andWhere('project.id IN (:clientProjectIds)')
                    ->setParameter('clientProjectIds', $projectIds);
            }
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
            $counts[$row['status']->value] = (int) $row['bugCount'];
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

    /**
     * @return array<int, array{userId: int, fullName: string, count: int}>
     */
    public function countResolvedByDeveloper(): array
    {
        $rows = $this->createQueryBuilder('bug')
            ->select('developer.id AS userId, developer.fullName AS fullName, COUNT(bug.id) AS bugCount')
            ->innerJoin('bug.assignedDeveloper', 'developer')
            ->where('bug.status IN (:statuses)')
            ->setParameter('statuses', [BugStatus::Closed, BugStatus::Rejected])
            ->groupBy('developer.id')
            ->addGroupBy('developer.fullName')
            ->orderBy('bugCount', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row): array => [
                'userId' => (int) $row['userId'],
                'fullName' => (string) $row['fullName'],
                'count' => (int) $row['bugCount'],
            ],
            $rows
        );
    }

    /**
     * @return array<int, array{bugId: int, title: string, developerName: string|null, timeSpentHours: float|null, treatedAt: \DateTimeImmutable|null, closedAt: \DateTimeImmutable|null, createdAt: \DateTimeImmutable}>
     */
    public function findTimeSpentPerBug(): array
    {
        $rows = $this->createQueryBuilder('bug')
            ->select('bug')
            ->leftJoin('bug.assignedDeveloper', 'developer')
            ->where('bug.openedAt IS NOT NULL')
            ->orderBy('bug.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $results = [];
        $now = new \DateTimeImmutable();

        foreach ($rows as $bug) {
            /** @var \App\Entity\BugReport $bug */
            $endpoint = $bug->getClosedAt() ?? $bug->getTreatedAt();
            $timeSpentHours = null;

            if ($bug->getOpenedAt() !== null) {
                $calcEnd = $endpoint ?? $now;
                $diff = $bug->getOpenedAt()->diff($calcEnd);
                $totalSeconds = abs($diff->s) + (abs($diff->i) * 60) + (abs($diff->h) * 3600) + (abs($diff->days) * 86400);
                $timeSpentHours = round($totalSeconds / 3600, 1);
            }

            $results[] = [
                'bugId' => $bug->getId(),
                'title' => $bug->getTitle(),
                'developerName' => $bug->getAssignedDeveloper()?->getFullName(),
                'timeSpentHours' => $timeSpentHours,
                'treatedAt' => $bug->getTreatedAt(),
                'closedAt' => $bug->getClosedAt(),
                'createdAt' => $bug->getCreatedAt(),
            ];
        }

        return $results;
    }
}
