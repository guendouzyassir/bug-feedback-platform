<?php

namespace App\Controller;

use App\Entity\BugReport;
use App\Entity\BugComment;
use App\Entity\User;
use App\Enum\BugPriority;
use App\Enum\BugStatus;
use App\Form\BugCommentType;
use App\Form\BugManagementType;
use App\Form\BugReportType;
use App\Form\BugStatusType;
use App\Repository\BugReportRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
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
    public function index(
        Request $request,
        BugReportRepository $bugReportRepository,
        ProjectRepository $projectRepository,
        UserRepository $userRepository,
    ): Response {
        [$filters, $filterValues] = $this->buildFilters($request);

        /** @var User $user */
        $user = $this->getUser();

        $canSeeAllBugs = $this->canSeeAllBugReports();
        $bugReports = $bugReportRepository->findVisibleWithFilters($user, $canSeeAllBugs, $filters);

        return $this->render('bug_report/index.html.twig', [
            'bug_reports' => $bugReports,
            'scope_label' => $canSeeAllBugs ? 'All bug reports' : 'My bug reports',
            'projects' => $projectRepository->findBy(['isActive' => true], ['name' => 'ASC']),
            'developers' => $userRepository->findDevelopers(),
            'statuses' => BugStatus::cases(),
            'priorities' => BugPriority::cases(),
            'filters' => $filterValues,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/manage', name: 'app_bug_report_manage', methods: ['GET', 'POST'])]
    public function manage(
        Request $request,
        BugReport $bugReport,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        FileUploader $fileUploader,
    ): Response {
        $form = $this->createForm(BugManagementType::class, $bugReport, [
            'developers' => $userRepository->findDevelopers(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($bugReport->getStatus() === BugStatus::Fixed) {
                $fileUploader->remove($bugReport->getScreenshotFilename());
                $entityManager->remove($bugReport);
                $entityManager->flush();
                $this->addFlash('success', 'Bug was fixed and has been removed.');

                return $this->redirectToRoute('app_bug_report_index', [], Response::HTTP_SEE_OTHER);
            }

            $bugReport->markOpened();

            if ($bugReport->getStatus() === BugStatus::InProgress) {
                $bugReport->markTreated();
            }

            $bugReport->touch();
            $entityManager->flush();

            $this->addFlash('success', 'Bug report management details updated.');

            return $this->redirectToRoute('app_bug_report_show', ['id' => $bugReport->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bug_report/manage.html.twig', [
            'bug_report' => $bugReport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/status', name: 'app_bug_report_status', methods: ['POST'])]
    public function updateStatus(Request $request, BugReport $bugReport, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        if (!$this->canUpdateBugStatus($bugReport)) {
            throw $this->createAccessDeniedException('You cannot update this bug report status.');
        }

        $form = $this->createStatusForm($bugReport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($bugReport->getStatus() === BugStatus::Fixed) {
                $fileUploader->remove($bugReport->getScreenshotFilename());
                $entityManager->remove($bugReport);
                $entityManager->flush();
                $this->addFlash('success', 'Bug was fixed and has been removed.');

                return $this->redirectToRoute('app_bug_report_index', [], Response::HTTP_SEE_OTHER);
            }

            $bugReport->markOpened();

            if ($bugReport->getStatus() === BugStatus::InProgress) {
                $bugReport->markTreated();
            }

            $entityManager->flush();
            $this->addFlash('success', 'Bug status updated.');
        } else {
            $this->addFlash('danger', 'The status update form is invalid.');
        }

        return $this->redirectToRoute('app_bug_report_show', ['id' => $bugReport->getId()], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/delete', name: 'app_bug_report_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        BugReport $bugReport,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
    ): Response {
        if (!$this->isCsrfTokenValid('delete'.$bugReport->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Invalid security token. Please try again.');

            return $this->redirectToRoute('app_bug_report_show', ['id' => $bugReport->getId()], Response::HTTP_SEE_OTHER);
        }

        $fileUploader->remove($bugReport->getScreenshotFilename());
        $entityManager->remove($bugReport);
        $entityManager->flush();

        $this->addFlash('success', 'Bug report deleted successfully.');

        return $this->redirectToRoute('app_bug_report_index', [], Response::HTTP_SEE_OTHER);
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
            'status_form' => $this->canUpdateBugStatus($bugReport) ? $this->createStatusForm($bugReport) : null,
            'can_manage' => $this->canManageBugReport(),
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
        return $this->canSeeAllBugReports()
            || $bugReport->getReporter()?->getId() === $this->getUser()?->getId();
    }

    private function canSeeAllBugReports(): bool
    {
        return $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_DEVELOPER');
    }

    private function canManageBugReport(): bool
    {
        return $this->isGranted('ROLE_ADMIN');
    }

    private function canUpdateBugStatus(BugReport $bugReport): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $this->isGranted('ROLE_DEVELOPER')
            && $bugReport->getAssignedDeveloper()?->getId() === $this->getUser()?->getId();
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function buildFilters(Request $request): array
    {
        $values = [
            'keyword' => trim((string) $request->query->get('keyword', '')),
            'project' => (string) $request->query->get('project', ''),
            'status' => (string) $request->query->get('status', ''),
            'priority' => (string) $request->query->get('priority', ''),
            'developer' => (string) $request->query->get('developer', ''),
            'dateFrom' => (string) $request->query->get('dateFrom', ''),
            'dateTo' => (string) $request->query->get('dateTo', ''),
        ];

        return [[
            'keyword' => $values['keyword'] !== '' ? $values['keyword'] : null,
            'project' => ctype_digit($values['project']) ? (int) $values['project'] : null,
            'status' => BugStatus::tryFrom($values['status']),
            'priority' => BugPriority::tryFrom($values['priority']),
            'developer' => ctype_digit($values['developer']) ? (int) $values['developer'] : null,
            'dateFrom' => $this->dateFilter($values['dateFrom']),
            'dateTo' => $this->dateFilter($values['dateTo']),
        ], $values];
    }

    private function dateFilter(string $value): ?\DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date instanceof \DateTimeImmutable ? $date : null;
    }

    private function createStatusForm(BugReport $bugReport)
    {
        return $this->createForm(BugStatusType::class, $bugReport, [
            'action' => $this->generateUrl('app_bug_report_status', ['id' => $bugReport->getId()]),
        ]);
    }
}
