<?php

namespace App\DataFixtures;

use App\Entity\BugComment;
use App\Entity\BugReport;
use App\Entity\ClientProfile;
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
        $admin = $this->createUser('admin@example.com', 'Admin User', ['ROLE_ADMIN'], 'admin');
        $manager->persist($admin);

        $developer = $this->createUser('dev@example.com', 'Test Developer', ['ROLE_DEVELOPER'], 'password123');
        $manager->persist($developer);

        $client = $this->createUser('client@example.com', 'Test Client', ['ROLE_CLIENT'], 'password123');
        $clientProfile = (new ClientProfile())
            ->setCompanyName('Acme Corp')
            ->setPhoneNumber('+213 555 123 456')
            ->setCompanyAddress('123 Main Street, Batna, Algeria');
        $client->setClientProfile($clientProfile);
        $manager->persist($client);

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
            $project->addAssignedDeveloper($developer);
            $manager->persist($project);
        }

        $client->addAssignedProject($projects['website']);
        $client->addAssignedProject($projects['mobile']);

        $bugs = [
            [
                'title' => 'Login page crashes on invalid email format',
                'description' => 'When a user enters an email without the @ symbol and clicks submit, the page throws a 500 error instead of showing a validation message.',
                'steps' => '1. Go to login page\n2. Enter "invalidemail" in the email field\n3. Enter any password\n4. Click Sign In',
                'expected' => 'A validation error message should appear below the email field.',
                'actual' => 'The page returns a 500 Internal Server Error.',
                'priority' => BugPriority::Critical,
                'status' => BugStatus::Fixed,
                'project' => $projects['website'],
            ],
            [
                'title' => 'Mobile app crashes when adding empty cart item',
                'description' => 'The mobile ordering app crashes when a user tries to add an item to the cart without selecting a size option.',
                'steps' => '1. Open the mobile app\n2. Browse to any product\n3. Do not select a size\n4. Tap "Add to Cart"',
                'expected' => 'The app should prompt the user to select a size first.',
                'actual' => 'The app crashes and shows a blank screen.',
                'priority' => BugPriority::High,
                'status' => BugStatus::InProgress,
                'project' => $projects['mobile'],
            ],
            [
                'title' => 'Contact form does not send email notifications',
                'description' => 'The contact form on the company website submits successfully but the admin never receives an email notification.',
                'steps' => '1. Go to the contact page\n2. Fill in all fields\n3. Submit the form\n4. Check admin email inbox',
                'expected' => 'Admin should receive an email notification within 1 minute.',
                'actual' => 'No email is received. The form shows success but nothing is sent.',
                'priority' => BugPriority::High,
                'status' => BugStatus::Open,
                'project' => $projects['website'],
            ],
            [
                'title' => 'CRM dashboard shows wrong date for closed tickets',
                'description' => 'The internal CRM dashboard displays tickets as "Closed Today" even when they were closed weeks ago.',
                'steps' => '1. Open the CRM dashboard\n2. Look at the "Recently Closed" section\n3. Compare dates with actual close dates',
                'expected' => 'Each ticket should show its actual close date.',
                'actual' => 'All closed tickets show today\'s date.',
                'priority' => BugPriority::Medium,
                'status' => BugStatus::Fixed,
                'project' => $projects['crm'],
            ],
            [
                'title' => 'API rate limiter returns 500 instead of 429',
                'description' => 'When the API rate limit is exceeded, the server returns a 500 Internal Server Error instead of the expected 429 Too Many Requests response.',
                'steps' => '1. Send 100 requests to /api/v1/users within 1 minute\n2. Observe the response after the limit is hit',
                'expected' => 'Response should be 429 with a Retry-After header.',
                'actual' => 'Response is 500 Internal Server Error with no useful body.',
                'priority' => BugPriority::Critical,
                'status' => BugStatus::Open,
                'project' => $projects['api'],
            ],
            [
                'title' => 'User avatar not displaying after profile update',
                'description' => 'After updating the user profile and uploading a new avatar, the old avatar is still shown until the browser cache is cleared.',
                'steps' => '1. Go to profile settings\n2. Upload a new avatar image\n3. Save changes\n4. Refresh the page',
                'expected' => 'The new avatar should display immediately after saving.',
                'actual' => 'The old avatar is shown. Only after Ctrl+F5 the new one appears.',
                'priority' => BugPriority::Low,
                'status' => BugStatus::Closed,
                'project' => $projects['website'],
            ],
            [
                'title' => 'Search functionality returns irrelevant results',
                'description' => 'The global search feature returns results that do not match the search query at all.',
                'steps' => '1. Click the search bar\n2. Type "invoice"\n3. Press Enter',
                'expected' => 'Results should contain items related to invoices.',
                'actual' => 'Results include unrelated items like user profiles and settings pages.',
                'priority' => BugPriority::Medium,
                'status' => BugStatus::InProgress,
                'project' => $projects['crm'],
            ],
            [
                'title' => 'Password reset link expires too quickly',
                'description' => 'The password reset link sent via email expires after 5 minutes, which is too short for users who don\'t check email immediately.',
                'steps' => '1. Request a password reset\n2. Wait 6 minutes\n3. Click the reset link in the email',
                'expected' => 'The link should be valid for at least 1 hour.',
                'actual' => 'The link shows "This reset link has expired."',
                'priority' => BugPriority::Low,
                'status' => BugStatus::Open,
                'project' => $projects['website'],
            ],
        ];

        foreach ($bugs as $bugData) {
            $bug = (new BugReport())
                ->setTitle($bugData['title'])
                ->setDescription($bugData['description'])
                ->setStepsToReproduce($bugData['steps'])
                ->setExpectedResult($bugData['expected'])
                ->setActualResult($bugData['actual'])
                ->setPriority($bugData['priority'])
                ->setStatus($bugData['status'])
                ->setProject($bugData['project'])
                ->setReporter($client);
            $bug->setOpenedAt(new \DateTimeImmutable('-5 days'));

            if (in_array($bugData['status']->value, ['fixed', 'closed', 'rejected'])) {
                $bug->setTreatedAt(new \DateTimeImmutable('-2 days'));
            }
            if ($bugData['status'] === BugStatus::Closed) {
                $bug->setStatus(BugStatus::Closed);
                $bug->setOpenedAt(new \DateTimeImmutable('-5 days'));
            }
            if ($bugData['status'] === BugStatus::Fixed) {
                $bug->setOpenedAt(new \DateTimeImmutable('-5 days'));
            }

            $manager->persist($bug);
        }

        $manager->flush();

        $allBugs = $manager->getRepository(BugReport::class)->findAll();

        $comments = [
            ['content' => 'I can reproduce this issue consistently. It seems to be a validation problem on the backend.', 'author' => $developer],
            ['content' => 'Thanks for the report. I\'ll look into this right away.', 'author' => $developer],
            ['content' => 'This is blocking our release. Can we prioritize the fix?', 'author' => $client],
            ['content' => 'Fix has been deployed to staging. Please verify when you get a chance.', 'author' => $developer],
            ['content' => 'Verified the fix on staging. Everything looks good now. Closing this ticket.', 'author' => $client],
        ];

        foreach ($allBugs as $index => $bug) {
            if ($index < count($comments)) {
                $comment = (new BugComment())
                    ->setContent($comments[$index]['content'])
                    ->setBugReport($bug)
                    ->setAuthor($comments[$index]['author']);
                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

    private function createUser(string $email, string $fullName, array $roles, string $plainPassword): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setFullName($fullName)
            ->setRoles($roles);

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        return $user;
    }
}
