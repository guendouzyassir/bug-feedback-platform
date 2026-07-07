<?php

namespace App\DataFixtures;

use App\Entity\BugComment;
use App\Entity\BugReport;
use App\Entity\Project;
use App\Entity\User;
use App\Enum\BugPriority;
use App\Enum\BugStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $this->createUser('admin@example.com', 'Admin User', ['ROLE_ADMIN']);
        $developer = $this->createUser('developer@example.com', 'Developer User', ['ROLE_DEVELOPER']);
        $developerTwo = $this->createUser('developer2@example.com', 'Second Developer', ['ROLE_DEVELOPER']);
        $client = $this->createUser('client@example.com', 'Client Tester', ['ROLE_CLIENT']);
        $clientTwo = $this->createUser('tester@example.com', 'QA Tester', ['ROLE_CLIENT']);

        foreach ([$admin, $developer, $developerTwo, $client, $clientTwo] as $user) {
            $manager->persist($user);
        }

        $projects = [
            'website' => (new Project())
                ->setName('Company Website')
                ->setPlatform('Web')
                ->setDescription('Marketing website, contact forms, and public pages.'),
            'mobile' => (new Project())
                ->setName('Mobile Ordering App')
                ->setPlatform('Mobile')
                ->setDescription('Customer mobile application for ordering and account management.'),
            'crm' => (new Project())
                ->setName('Internal CRM')
                ->setPlatform('Web')
                ->setDescription('Internal dashboard used by sales and support teams.'),
            'api' => (new Project())
                ->setName('Partner API')
                ->setPlatform('API')
                ->setDescription('REST API used by external integration partners.'),
        ];

        foreach ($projects as $project) {
            $manager->persist($project);
        }

        $bugReports = [
            $this->createBugReport(
                $projects['website'],
                $client,
                $developer,
                'Contact form shows success but email is not received',
                'The contact page displays a success message, but no email arrives in the support inbox.',
                BugPriority::High,
                BugStatus::InProgress,
                "1. Open the contact page\n2. Fill the form\n3. Submit the form\n4. Check support inbox",
                'The support team receives the message by email.',
                'The website displays success, but no email arrives.'
            ),
            $this->createBugReport(
                $projects['mobile'],
                $client,
                $developerTwo,
                'Checkout button stays disabled after selecting delivery address',
                'The mobile checkout screen does not enable the payment button after a valid delivery address is selected.',
                BugPriority::Critical,
                BugStatus::Open,
                "1. Open cart\n2. Choose delivery\n3. Select saved address",
                'Payment button becomes active.',
                'Payment button remains disabled.'
            ),
            $this->createBugReport(
                $projects['crm'],
                $clientTwo,
                $developer,
                'Customer search is case-sensitive',
                'Searching for customer names only works when the exact letter case is used.',
                BugPriority::Medium,
                BugStatus::Fixed,
                "1. Open CRM customers\n2. Search for a lowercase customer name",
                'Search returns matching customers regardless of case.',
                'No customers are returned for lowercase input.'
            ),
            $this->createBugReport(
                $projects['api'],
                $clientTwo,
                $developerTwo,
                'API returns 500 when optional phone number is missing',
                'Creating a partner lead without a phone number triggers a server error.',
                BugPriority::High,
                BugStatus::InProgress,
                'Send POST /leads without phoneNumber.',
                'API returns 201 Created.',
                'API returns 500 Internal Server Error.'
            ),
            $this->createBugReport(
                $projects['website'],
                $client,
                null,
                'Homepage hero image is cropped on tablet screens',
                'The main image loses the product area on medium-width screens.',
                BugPriority::Low,
                BugStatus::Open,
                'Open the homepage on a tablet-size viewport.',
                'Image remains readable and centered.',
                'Important visual content is cropped.'
            ),
            $this->createBugReport(
                $projects['mobile'],
                $clientTwo,
                $developer,
                'Push notification opens the wrong order',
                'Tapping an order update notification opens the previous order instead of the updated order.',
                BugPriority::Critical,
                BugStatus::Rejected,
                'Tap a notification after placing two orders.',
                'The updated order opens.',
                'The previous order opens.'
            ),
            $this->createBugReport(
                $projects['crm'],
                $client,
                null,
                'Exported CSV uses unreadable date format',
                'The customer export uses timestamps that are difficult for support agents to read.',
                BugPriority::Medium,
                BugStatus::Open,
                'Export the customer list to CSV.',
                'Dates use YYYY-MM-DD format.',
                'Dates use raw timestamps.'
            ),
            $this->createBugReport(
                $projects['api'],
                $clientTwo,
                $developerTwo,
                'Rate limit headers are missing',
                'Partner applications cannot display remaining request limits because headers are not included.',
                BugPriority::Medium,
                BugStatus::Fixed,
                'Call any authenticated API endpoint.',
                'Response includes rate limit headers.',
                'Headers are missing.'
            ),
            $this->createBugReport(
                $projects['website'],
                $client,
                $developer,
                'Password reset link expires immediately',
                'Some reset links are rejected as expired right after being generated.',
                BugPriority::Critical,
                BugStatus::Closed,
                'Request a password reset and click the email link.',
                'The reset form opens.',
                'Expired-link error appears.'
            ),
            $this->createBugReport(
                $projects['mobile'],
                $clientTwo,
                null,
                'Profile avatar upload accepts unsupported file type',
                'The avatar field allows selecting a PDF file.',
                BugPriority::High,
                BugStatus::Open,
                'Open profile edit and select a PDF as avatar.',
                'Only image files are accepted.',
                'The file picker accepts the PDF.'
            ),
        ];

        foreach ($bugReports as $bugReport) {
            $manager->persist($bugReport);
        }

        $comments = [
            $this->createComment($bugReports[0], $developer, 'I reproduced this and started checking the mailer configuration.'),
            $this->createComment($bugReports[0], $client, 'This happens in production and staging.'),
            $this->createComment($bugReports[1], $admin, 'This is blocking mobile checkout, keeping it critical.'),
            $this->createComment($bugReports[2], $developer, 'Case-insensitive search was added and verified locally.'),
            $this->createComment($bugReports[3], $developerTwo, 'The API validation layer is being updated.'),
            $this->createComment($bugReports[5], $developer, 'Rejected after review: the notification payload belongs to an old app version.'),
            $this->createComment($bugReports[8], $admin, 'Closed after confirming the reset token expiration fix.'),
        ];

        foreach ($comments as $comment) {
            $manager->persist($comment);
        }

        $manager->flush();
    }

    private function createUser(string $email, string $fullName, array $roles): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setFullName($fullName)
            ->setRoles($roles);

        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

        return $user;
    }

    private function createBugReport(
        Project $project,
        User $reporter,
        ?User $assignedDeveloper,
        string $title,
        string $description,
        BugPriority $priority,
        BugStatus $status,
        ?string $stepsToReproduce,
        ?string $expectedResult,
        ?string $actualResult,
    ): BugReport {
        return (new BugReport())
            ->setProject($project)
            ->setReporter($reporter)
            ->setAssignedDeveloper($assignedDeveloper)
            ->setTitle($title)
            ->setDescription($description)
            ->setStepsToReproduce($stepsToReproduce)
            ->setExpectedResult($expectedResult)
            ->setActualResult($actualResult)
            ->setPriority($priority)
            ->setStatus($status);
    }

    private function createComment(BugReport $bugReport, User $author, string $content): BugComment
    {
        return (new BugComment())
            ->setBugReport($bugReport)
            ->setAuthor($author)
            ->setContent($content);
    }
}
