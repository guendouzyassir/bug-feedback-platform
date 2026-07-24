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

        $allAssigned = $bugReportRepository->count(['assignedDeveloper' => $user]);
        $inProgress = $bugReportRepository->count(['assignedDeveloper' => $user, 'status' => BugStatus::InProgress]);
        $open = $bugReportRepository->count(['assignedDeveloper' => $user, 'status' => BugStatus::Open]);
        $fixed = $bugReportRepository->count(['assignedDeveloper' => $user, 'status' => BugStatus::Fixed]);

        return $this->render('developer/dashboard.html.twig', [
            'assignedBugCount' => $allAssigned,
            'inProgressCount' => $inProgress,
            'openCount' => $open,
            'fixedCount' => $fixed,
        ]);
    }
}