<?php

namespace App\Controller\Admin;

use App\Entity\BugComment;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/users')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'is_create' => true,
            'current_role' => 'ROLE_DEVELOPER',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyRoleFromForm($user, $form->get('role')->getData());
            $user->setPassword($passwordHasher->hashPassword($user, (string) $form->get('plainPassword')->getData()));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User account created successfully.');

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $form = $this->createForm(UserType::class, $user, [
            'current_role' => $this->primaryRole($user),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $editingSelf = $user->getId() === $this->getUser()?->getId();
            if ($editingSelf) {
                $user->setIsActive(true);
                $this->addFlash('danger', 'You cannot deactivate or change the role of your own account.');
            } else {
                $this->applyRoleFromForm($user, $form->get('role')->getData());
            }

            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword !== null && $plainPassword !== '') {
                $user->setPassword($passwordHasher->hashPassword($user, (string) $plainPassword));
            }

            $entityManager->flush();

            $this->addFlash('success', 'User account updated successfully.');

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
    ): Response {
        if (!$this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Invalid security token. Please try again.');

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($user->getId() === $this->getUser()?->getId()) {
            $this->addFlash('danger', 'You cannot delete your own account.');

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        $this->removeUserDependencies($user, $entityManager, $fileUploader);
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'User account deleted successfully.');

        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    private function applyRoleFromForm(User $user, string $role): void
    {
        $user->setRoles([$role]);
    }

    private function primaryRole(User $user): string
    {
        foreach (['ROLE_ADMIN', 'ROLE_DEVELOPER', 'ROLE_CLIENT'] as $role) {
            if (in_array($role, $user->getRoles(), true)) {
                return $role;
            }
        }

        return 'ROLE_CLIENT';
    }

    private function removeUserDependencies(
        User $user,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
    ): void {
        $reportedBugReports = [];
        foreach ($user->getReportedBugReports() as $bugReport) {
            $reportedBugReports[spl_object_id($bugReport)] = $bugReport;
        }

        foreach ($user->getAssignedBugReports() as $bugReport) {
            if (isset($reportedBugReports[spl_object_id($bugReport)])) {
                continue;
            }

            $bugReport
                ->setAssignedDeveloper(null)
                ->touch();
        }

        foreach ($reportedBugReports as $bugReport) {
            $fileUploader->remove($bugReport->getScreenshotFilename());
            $entityManager->remove($bugReport);
        }

        foreach ($user->getComments() as $comment) {
            /** @var BugComment $comment */
            $bugReport = $comment->getBugReport();

            if ($bugReport !== null && isset($reportedBugReports[spl_object_id($bugReport)])) {
                continue;
            }

            $entityManager->remove($comment);
        }
    }
}
