<?php

namespace App\Tests\Controller;

use App\Entity\BugReport;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BugWorkflowTest extends WebTestCase
{
    private KernelBrowser $browser;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->browser = static::createClient();
        $this->browser->disableReboot();
        static::getContainer()->get('cache.rate_limiter')->clear();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Never create test tables in a configured application database.
        self::assertTrue($this->entityManager->getConnection()->getParams()['memory'] ?? false);
        (new SchemaTool($this->entityManager))->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
    }

    public function testDeveloperCanCreateAndWorkOnlyInAssignedProjects(): void
    {
        $developer = $this->user('developer');
        $other = $this->user('other');
        $project = $this->project('Reported project')->addAssignedDeveloper($developer);
        $privateProject = $this->project('Private project')->addAssignedDeveloper($other);
        // Previous bug assignment and authorship must not grant project access.
        $privateBug = $this->bug('Other developer issue', $privateProject, $developer, $developer);
        $this->entityManager->flush();
        $this->browser->loginUser($developer);

        $crawler = $this->browser->request('GET', '/bugs/new');
        self::assertResponseIsSuccessful();
        self::assertSame(['Reported project'], $crawler->filter('select[name="bug_report[project]"] option')->extract(['_text']));
        $this->browser->submit($crawler->selectButton('Create Bug Report')->form([
            'bug_report[project]' => $project->getId(),
            'bug_report[title]' => 'New developer report',
            'bug_report[description]' => 'Regression: the reporter must be able to follow this bug.',
        ]), [], ['HTTP_ORIGIN' => 'http://localhost']);
        self::assertResponseStatusCodeSame(303);

        $this->browser->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'New developer report');
        $reported = $this->entityManager->getRepository(BugReport::class)->findOneBy(['title' => 'New developer report']);
        self::assertNotNull($reported);
        self::assertNull($reported->getAssignedDeveloper());

        $this->browser->request('GET', '/bugs');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'New developer report');
        self::assertSelectorTextNotContains('body', 'Other developer issue');

        $crawler = $this->browser->request('GET', '/bugs/'.$reported->getId());
        $this->browser->submit($crawler->selectButton('Update Status')->form(['bug_status[status]' => 'in_progress']), [], ['HTTP_ORIGIN' => 'http://localhost']);
        self::assertResponseStatusCodeSame(303);
        $this->browser->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('option[value="in_progress"][selected]');

        $this->browser->request('GET', '/bugs/'.$privateBug->getId());
        self::assertResponseStatusCodeSame(403);
        $this->browser->request('POST', '/bugs/'.$privateBug->getId(), ['bug_comment' => ['content' => 'Forbidden comment']]);
        self::assertResponseStatusCodeSame(403);
        $this->browser->request('POST', '/bugs/'.$privateBug->getId().'/status', ['bug_status' => ['status' => 'fixed']]);
        self::assertResponseStatusCodeSame(403);
        $this->browser->request('GET', '/bugs/'.$privateBug->getId().'/screenshot');
        self::assertResponseStatusCodeSame(403);

        $crawler = $this->browser->request('GET', '/bugs/new');
        $payload = $crawler->selectButton('Create Bug Report')->form()->getPhpValues();
        $payload['bug_report']['project'] = $privateProject->getId();
        $payload['bug_report']['title'] = 'Forged report';
        $payload['bug_report']['description'] = 'Must never be saved';
        $this->browser->request('POST', '/bugs/new', $payload, [], ['HTTP_ORIGIN' => 'http://localhost']);
        self::assertResponseStatusCodeSame(422);
        self::assertNull($this->entityManager->getRepository(BugReport::class)->findOneBy(['title' => 'Forged report']));
    }

    public function testDeveloperProjectFilterUsesVisibleBugsAndDoesNotLeakOtherProjects(): void
    {
        $developer = $this->user('developer');
        $other = $this->user('other');
        $assignedProject = $this->project('Assigned project')->addAssignedDeveloper($developer);
        $reportedProject = $this->project('Reported project')->addAssignedDeveloper($developer);
        $archivedProject = $this->project('Archived project')->addAssignedDeveloper($developer)->setIsActive(false);
        $this->project('Empty assigned project')->addAssignedDeveloper($developer);
        $privateProject = $this->project('Private project');
        $this->bug('Assigned one', $assignedProject, $other, $developer);
        $this->bug('Assigned two', $assignedProject, $other, $developer);
        $this->bug('Reported one', $reportedProject, $other, null);
        $this->bug('Archived one', $archivedProject, $other, $developer);
        $this->bug('Private one', $privateProject, $other, $other);
        $this->entityManager->flush();
        self::assertCount(0, $developer->getAssignedProjects());
        $this->browser->loginUser($developer);

        $crawler = $this->browser->request('GET', '/bugs');
        self::assertResponseIsSuccessful();
        $options = $crawler->filter('select[name="project"] option')->extract(['_text']);
        self::assertContains('Assigned project', $options);
        self::assertContains('Reported project', $options);
        self::assertContains('Archived project', $options);
        self::assertContains('Empty assigned project', $options);
        self::assertNotContains('Private project', $options);
        self::assertSame(1, array_count_values($options)['Assigned project']);

        $this->browser->request('GET', '/bugs', ['project' => $reportedProject->getId()]);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Reported one');
        self::assertSelectorTextNotContains('body', 'Assigned one');
        $this->browser->request('GET', '/bugs', ['project' => $privateProject->getId()]);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextNotContains('body', 'Private one');

        $crawler = $this->browser->request('GET', '/developer/dashboard');
        self::assertResponseIsSuccessful();
        self::assertSame('4', trim($crawler->filter('.metric-value')->first()->text()));
        self::assertSelectorTextContains('body', 'Empty assigned project');
        self::assertSelectorTextNotContains('body', 'Private project');
    }

    public function testDeveloperWithoutProjectsCannotCreateOrAccessLegacyBugs(): void
    {
        $developer = $this->user('unassigned');
        $project = $this->project('Hidden project');
        $bug = $this->bug('Legacy bug', $project, $developer, $developer);
        $this->entityManager->flush();
        $this->browser->loginUser($developer);

        $this->browser->request('GET', '/bugs');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextNotContains('body', 'Legacy bug');
        self::assertSelectorNotExists('a[href="/bugs/new"]');
        $this->browser->request('GET', '/bugs/new');
        self::assertResponseStatusCodeSame(403);
        $this->browser->request('GET', '/bugs/'.$bug->getId());
        self::assertResponseStatusCodeSame(403);
        $this->browser->request('GET', '/developer/dashboard');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'No projects assigned yet');
    }

    public function testAdminCanAssignAndRevokeDevelopersAndRestrictBugAssignment(): void
    {
        $admin = $this->user('admin')->setRoles(['ROLE_ADMIN']);
        $developer = $this->user('developer');
        $outsider = $this->user('outsider');
        $project = $this->project('Managed project');
        $bug = $this->bug('Managed issue', $project, $developer, null);
        $this->entityManager->flush();
        $this->browser->loginUser($admin);

        $crawler = $this->browser->request('GET', '/admin/projects/'.$project->getId().'/edit');
        self::assertResponseIsSuccessful();
        $this->browser->submit($crawler->selectButton('Update Project')->form([
            'project[assignedDevelopers]' => [(string) $developer->getId()],
        ]), [], ['HTTP_ORIGIN' => 'http://localhost']);
        self::assertResponseStatusCodeSame(303);
        $this->entityManager->clear();
        $project = $this->entityManager->find(Project::class, $project->getId());
        $developer = $this->entityManager->find(User::class, $developer->getId());
        self::assertTrue($project->isDeveloperAssigned($developer));

        $crawler = $this->browser->request('GET', '/bugs/'.$bug->getId().'/manage');
        self::assertResponseIsSuccessful();
        $choices = $crawler->filter('select[name="bug_management[assignedDeveloper]"] option')->extract(['value']);
        self::assertContains((string) $developer->getId(), $choices);
        self::assertNotContains((string) $outsider->getId(), $choices);
        $form = $crawler->filter('form')->first()->form();
        $payload = $form->getPhpValues();
        $payload['bug_management']['assignedDeveloper'] = $outsider->getId();
        $this->browser->request('POST', '/bugs/'.$bug->getId().'/manage', $payload, [], ['HTTP_ORIGIN' => 'http://localhost']);
        self::assertResponseStatusCodeSame(422);

        $crawler = $this->browser->request('GET', '/bugs/'.$bug->getId().'/manage');
        $this->browser->submit($crawler->filter('form')->first()->form([
            'bug_management[assignedDeveloper]' => (string) $developer->getId(),
        ]), [], ['HTTP_ORIGIN' => 'http://localhost']);
        self::assertResponseStatusCodeSame(303);

        $this->browser->loginUser($developer);
        $this->browser->request('GET', '/bugs/'.$bug->getId());
        self::assertResponseIsSuccessful();
        $this->browser->request('GET', '/admin/projects/'.$project->getId().'/edit');
        self::assertResponseStatusCodeSame(403);

        $this->browser->loginUser($admin);
        $crawler = $this->browser->request('GET', '/admin/projects/'.$project->getId().'/edit');
        $payload = $crawler->selectButton('Update Project')->form()->getPhpValues();
        unset($payload['project']['assignedDevelopers']);
        $this->browser->request('POST', '/admin/projects/'.$project->getId().'/edit', $payload, [], ['HTTP_ORIGIN' => 'http://localhost']);
        self::assertResponseStatusCodeSame(303);
        $this->entityManager->clear();
        $developer = $this->entityManager->find(User::class, $developer->getId());
        self::assertCount(0, $developer->getDevelopmentProjects());
        self::assertNull($this->entityManager->find(BugReport::class, $bug->getId())->getAssignedDeveloper());
        $this->browser->loginUser($developer);
        $this->browser->request('GET', '/bugs/'.$bug->getId());
        self::assertResponseStatusCodeSame(403);
        $this->browser->request('POST', '/bugs/'.$bug->getId().'/status');
        self::assertResponseStatusCodeSame(403);
    }

    public function testClientAssignmentsRemainSeparateFromDeveloperAssignments(): void
    {
        $client = $this->user('client')->setRoles(['ROLE_CLIENT']);
        $developer = $this->user('developer');
        $clientProject = $this->project('Client project')->addAssignedClient($client);
        $devProject = $this->project('Developer project')->addAssignedDeveloper($developer);
        $visible = $this->bug('Client visible bug', $clientProject, $developer, null);
        $hidden = $this->bug('Developer private bug', $devProject, $client, $developer);
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->browser->loginUser($this->entityManager->find(User::class, $client->getId()));

        $crawler = $this->browser->request('GET', '/bugs/new');
        self::assertResponseIsSuccessful();
        self::assertSame(['Client project'], $crawler->filter('select[name="bug_report[project]"] option')->extract(['_text']));
        $this->browser->request('GET', '/bugs/'.$visible->getId());
        self::assertResponseIsSuccessful();
        $this->browser->request('POST', '/bugs/'.$visible->getId().'/status');
        self::assertResponseStatusCodeSame(403);
        $this->browser->request('GET', '/bugs/'.$hidden->getId());
        self::assertResponseStatusCodeSame(403);
    }

    public function testFiveFailedLoginsBlockEvenCorrectCredentialsOnTheNextAttempt(): void
    {
        $user = $this->user('login');
        $email = $user->getEmail();
        $this->entityManager->flush();

        for ($attempt = 0; $attempt < 5; ++$attempt) {
            $this->login($email, 'wrong-password');
            self::assertResponseRedirects('/login');
            $this->browser->followRedirect();
            self::assertSelectorTextNotContains('.alert-danger', 'Too many failed login attempts');
        }

        $this->login($email, 'test-password');
        self::assertResponseRedirects('/login');
        $this->browser->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Too many failed login attempts');

        // A different IP gets a separate allowance and valid credentials still work.
        $this->browser->setServerParameter('REMOTE_ADDR', '127.0.0.2');
        $this->login($email, 'test-password');
        self::assertResponseRedirects('http://localhost/');
    }

    private function login(string $email, string $password): void
    {
        $crawler = $this->browser->request('GET', '/login');
        $this->browser->submit($crawler->selectButton('Sign in')->form([
            'email' => $email,
            'password' => $password,
        ]), [], ['HTTP_ORIGIN' => 'http://localhost']);
    }

    private function user(string $name): User
    {
        $user = (new User())->setFullName($name)->setEmail($name.'@example.test')
            ->setRoles(['ROLE_DEVELOPER'])->setPassword(password_hash('test-password', PASSWORD_BCRYPT, ['cost' => 4]));
        $this->entityManager->persist($user);

        return $user;
    }

    private function project(string $name): Project
    {
        $project = (new Project())->setName($name)->setPlatform('Web');
        $this->entityManager->persist($project);

        return $project;
    }

    private function bug(string $title, Project $project, User $reporter, ?User $developer): BugReport
    {
        $bug = (new BugReport())->setTitle($title)->setDescription('Test description')
            ->setProject($project)->setReporter($reporter)->setAssignedDeveloper($developer);
        $this->entityManager->persist($bug);

        return $bug;
    }
}
