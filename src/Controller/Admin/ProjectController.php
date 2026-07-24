<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Entity\User;
use App\Form\ClientProjectType;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/projects')]
final class ProjectController extends AbstractController
{

    #[Route(name: 'app_admin_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        return $this->render('admin/project/index.html.twig', [
            'projects' => $projectRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_admin_project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($project);
            $entityManager->flush();

            $this->addFlash('success', 'Project created successfully.');

            return $this->redirectToRoute('app_admin_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('admin/project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project->touch();
            $entityManager->flush();

            $this->addFlash('success', 'Project updated successfully.');

            return $this->redirectToRoute('app_admin_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/assign-clients', name: 'app_admin_project_assign_clients', methods: ['GET', 'POST'])]
    public function assignClients(
        Request $request,
        Project $project,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
    ): Response {
        $clients = array_filter(
            $userRepository->findBy(['isActive' => true], ['fullName' => 'ASC']),
            fn (User $user) => $user->isClient()
        );

        $form = $this->createForm(ClientProjectType::class, $project, [
            'clients' => $clients,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Client assignments updated for "'.$project->getName().'."');

            return $this->redirectToRoute('app_admin_project_show', ['id' => $project->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project/assign_clients.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_project_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Project $project,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
    ): Response {
        if (!$this->isCsrfTokenValid('delete'.$project->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Invalid security token. Please try again.');

            return $this->redirectToRoute('app_admin_project_index', [], Response::HTTP_SEE_OTHER);
        }

        foreach ($project->getBugReports() as $bugReport) {
            $fileUploader->remove($bugReport->getScreenshotFilename());
            $entityManager->remove($bugReport);
        }

        $entityManager->remove($project);
        $entityManager->flush();

        $this->addFlash('success', 'Project deleted successfully.');

        return $this->redirectToRoute('app_admin_project_index', [], Response::HTTP_SEE_OTHER);
    }
}