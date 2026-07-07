<?php

namespace App\Controller\Developer;

use App\Entity\User;
use App\Repository\BugReportRepository;
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

        return $this->render('developer/dashboard.html.twig', [
            'assignedBugCount' => $bugReportRepository->count(['assignedDeveloper' => $user]),
        ]);
    }
}
