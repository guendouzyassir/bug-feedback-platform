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
        $client = $this->createUser('client@example.com', 'Client Tester', ['ROLE_CLIENT']);

        $manager->persist($admin);
        $manager->persist($developer);
        $manager->persist($client);

        $webProject = (new Project())
            ->setName('Company Website')
            ->setPlatform('Web')
            ->setDescription('Marketing website and client contact forms.');

        $mobileProject = (new Project())
            ->setName('Mobile Ordering App')
            ->setPlatform('Mobile')
            ->setDescription('Internal demo mobile application for bug reporting workflow.');

        $manager->persist($webProject);
        $manager->persist($mobileProject);

        $bugReport = (new BugReport())
            ->setProject($webProject)
            ->setReporter($client)
            ->setAssignedDeveloper($developer)
            ->setTitle('Contact form shows success but email is not received')
            ->setDescription('The contact page displays a success message, but no email arrives in the support inbox.')
            ->setStepsToReproduce("1. Open the contact page\n2. Fill the form\n3. Submit the form\n4. Check support inbox")
            ->setExpectedResult('The support team receives the message by email.')
            ->setActualResult('The website displays success, but no email arrives.')
            ->setPriority(BugPriority::High)
            ->setStatus(BugStatus::InProgress);

        $manager->persist($bugReport);

        $comment = (new BugComment())
            ->setBugReport($bugReport)
            ->setAuthor($developer)
            ->setContent('I reproduced this on the development environment and started checking the mailer configuration.');

        $manager->persist($comment);

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
}
