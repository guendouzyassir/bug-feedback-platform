<?php

namespace App\Controller\Admin;

use App\Enum\BugPriority;
use App\Enum\BugStatus;
use App\Repository\BugReportRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(
        UserRepository $userRepository,
        ProjectRepository $projectRepository,
        BugReportRepository $bugReportRepository,
    ): Response {
        $statusCounts = $bugReportRepository->countByStatus();

        return $this->render('admin/dashboard.html.twig', [
            'userCount' => $userRepository->count([]),
            'projectCount' => $projectRepository->count([]),
            'bugCount' => $bugReportRepository->count([]),
            'openBugCount' => $statusCounts[BugStatus::Open->value] ?? 0,
            'inProgressBugCount' => $statusCounts[BugStatus::InProgress->value] ?? 0,
            'fixedBugCount' => $statusCounts[BugStatus::Fixed->value] ?? 0,
            'criticalBugCount' => $bugReportRepository->count(['priority' => BugPriority::Critical]),
            'unassignedBugCount' => $bugReportRepository->count(['assignedDeveloper' => null]),
            'bugsByStatus' => array_map(
                static fn (BugStatus $status): array => [
                    'label' => $status->label(),
                    'count' => $statusCounts[$status->value] ?? 0,
                ],
                BugStatus::cases(),
            ),
            'bugsByProject' => $bugReportRepository->countByProject(),
        ]);
    }
}
