<?php

namespace App\Controller\Developer;

use App\Entity\User;
use App\Repository\BugReportRepository;
use App\Enum\BugStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/developer')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'developer_dashboard')]
    public function index(BugReportRepository $bugReportRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $counts = $bugReportRepository->countByStatus($user);

        return $this->render('developer/dashboard.html.twig', [
            'projectBugCount' => array_sum($counts),
            'inProgressCount' => $counts[BugStatus::InProgress->value] ?? 0,
            'openCount' => $counts[BugStatus::Open->value] ?? 0,
            'fixedCount' => $counts[BugStatus::Fixed->value] ?? 0,
            'assignedProjects' => $user->getDevelopmentProjects(),
        ]);
    }
}
