<?php

namespace App\Controller;

use App\Entity\BugReport;
use App\Entity\BugComment;
use App\Entity\User;
use App\Enum\BugStatus;
use App\Form\BugCommentType;
use App\Form\BugReportType;
use App\Repository\BugReportRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/bugs')]
final class BugReportController extends AbstractController
{
    #[Route(name: 'app_bug_report_index', methods: ['GET'])]
    public function index(BugReportRepository $bugReportRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $canSeeAllBugs = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_DEVELOPER');
        $bugReports = $canSeeAllBugs
            ? $bugReportRepository->findBy([], ['createdAt' => 'DESC'])
            : $bugReportRepository->findBy(['reporter' => $user], ['createdAt' => 'DESC']);

        return $this->render('bug_report/index.html.twig', [
            'bug_reports' => $bugReports,
            'scope_label' => $canSeeAllBugs ? 'All bug reports' : 'My bug reports',
        ]);
    }

    #[IsGranted('ROLE_CLIENT')]
    #[Route('/new', name: 'app_bug_report_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $bugReport = new BugReport();
        $form = $this->createForm(BugReportType::class, $bugReport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bugReport
                ->setReporter($user)
                ->setStatus(BugStatus::Open);

            $screenshotFile = $form->get('screenshot')->getData();
            if ($screenshotFile !== null) {
                $bugReport->setScreenshotFilename($fileUploader->upload($screenshotFile));
            }

            $entityManager->persist($bugReport);
            $entityManager->flush();

            $this->addFlash('success', 'Bug report created successfully.');

            return $this->redirectToRoute('app_bug_report_show', ['id' => $bugReport->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bug_report/new.html.twig', [
            'bug_report' => $bugReport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bug_report_show', methods: ['GET', 'POST'])]
    public function show(Request $request, BugReport $bugReport, EntityManagerInterface $entityManager): Response
    {
        if (!$this->canViewBugReport($bugReport)) {
            throw $this->createAccessDeniedException('You cannot view this bug report.');
        }

        /** @var User $user */
        $user = $this->getUser();
        $comment = new BugComment();
        $commentForm = $this->createForm(BugCommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment
                ->setBugReport($bugReport)
                ->setAuthor($user);

            $bugReport->touch();

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Comment added successfully.');

            return $this->redirectToRoute('app_bug_report_show', ['id' => $bugReport->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bug_report/show.html.twig', [
            'bug_report' => $bugReport,
            'comment_form' => $commentForm,
        ]);
    }

    #[Route('/{id}/screenshot', name: 'app_bug_report_screenshot', methods: ['GET'])]
    public function screenshot(BugReport $bugReport, FileUploader $fileUploader): Response
    {
        if (!$this->canViewBugReport($bugReport)) {
            throw $this->createAccessDeniedException('You cannot view this screenshot.');
        }

        $filename = $bugReport->getScreenshotFilename();
        if ($filename === null) {
            throw $this->createNotFoundException('This bug report has no screenshot.');
        }

        try {
            $path = $fileUploader->getPath($filename);
        } catch (FileException) {
            throw $this->createNotFoundException('Screenshot file is invalid.');
        }

        if (!is_file($path)) {
            throw $this->createNotFoundException('Screenshot file was not found.');
        }

        return $this->file($path, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    private function canViewBugReport(BugReport $bugReport): bool
    {
        return $this->isGranted('ROLE_ADMIN')
            || $this->isGranted('ROLE_DEVELOPER')
            || $bugReport->getReporter()?->getId() === $this->getUser()?->getId();
    }
}
