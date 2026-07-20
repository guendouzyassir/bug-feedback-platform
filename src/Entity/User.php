<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'app_user')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private ?string $fullName = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, BugReport>
     */
    #[ORM\OneToMany(mappedBy: 'reporter', targetEntity: BugReport::class)]
    private Collection $reportedBugReports;

    /**
     * @var Collection<int, BugReport>
     */
    #[ORM\OneToMany(mappedBy: 'assignedDeveloper', targetEntity: BugReport::class)]
    private Collection $assignedBugReports;

    /**
     * @var Collection<int, BugComment>
     */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: BugComment::class)]
    private Collection $comments;

    #[ORM\OneToOne(targetEntity: ClientProfile::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?ClientProfile $clientProfile = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->reportedBugReports = new ArrayCollection();
        $this->assignedBugReports = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->fullName ?? $this->email ?? 'User';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower($email);

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, BugReport>
     */
    public function getReportedBugReports(): Collection
    {
        return $this->reportedBugReports;
    }

    /**
     * @return Collection<int, BugReport>
     */
    public function getAssignedBugReports(): Collection
    {
        return $this->assignedBugReports;
    }

    /**
     * @return Collection<int, BugComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getClientProfile(): ?ClientProfile
    {
        return $this->clientProfile;
    }

    public function setClientProfile(?ClientProfile $clientProfile): static
    {
        if ($clientProfile === $this->clientProfile) {
            return $this;
        }

        $this->clientProfile = $clientProfile;

        if ($clientProfile !== null && $clientProfile->getUser() !== $this) {
            $clientProfile->setUser($this);
        }

        return $this;
    }
}
