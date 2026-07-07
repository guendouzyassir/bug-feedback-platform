<?php

namespace App\Controller\Admin;

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
        return $this->render('admin/dashboard.html.twig', [
            'userCount' => $userRepository->count([]),
            'projectCount' => $projectRepository->count([]),
            'bugCount' => $bugReportRepository->count([]),
        ]);
    }
}
