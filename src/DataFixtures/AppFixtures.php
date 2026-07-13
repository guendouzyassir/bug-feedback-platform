<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\User;
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
            $manager->persist($project);
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
