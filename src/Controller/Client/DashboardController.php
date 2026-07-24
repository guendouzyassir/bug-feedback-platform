<?php

namespace App\Controller\Client;

use App\Entity\User;
use App\Repository\BugReportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'client_dashboard')]
    public function index(BugReportRepository $bugReportRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $assignedProjects = $user->getAssignedProjects();

        $projectBugCounts = [];
        foreach ($assignedProjects as $project) {
            $projectBugCounts[$project->getId()] = $bugReportRepository->count(['project' => $project]);
        }

        return $this->render('client/dashboard.html.twig', [
            'reportedBugCount' => $bugReportRepository->count(['reporter' => $user]),
            'assignedProjects' => $assignedProjects,
            'projectBugCounts' => $projectBugCounts,
        ]);
    }
}