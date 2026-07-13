<?php

namespace App\Entity;

use App\Enum\BugPriority;
use App\Enum\BugStatus;
use App\Repository\BugReportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BugReportRepository::class)]
#[ORM\Index(name: 'IDX_BUG_REPORT_STATUS', fields: ['status'])]
#[ORM\Index(name: 'IDX_BUG_REPORT_PRIORITY', fields: ['priority'])]
#[ORM\Index(name: 'IDX_BUG_REPORT_CREATED_AT', fields: ['createdAt'])]
#[ORM\Index(name: 'IDX_BUG_REPORT_OPENED_AT', fields: ['openedAt'])]
#[ORM\Index(name: 'IDX_BUG_REPORT_TREATED_AT', fields: ['treatedAt'])]
class BugReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $stepsToReproduce = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $expectedResult = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $actualResult = null;

    #[ORM\Column(enumType: BugPriority::class)]
    private BugPriority $priority = BugPriority::Medium;

    #[ORM\Column(enumType: BugStatus::class)]
    private BugStatus $status = BugStatus::Open;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $screenshotFilename = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $openedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $treatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $closedAt = null;

    #[ORM\ManyToOne(inversedBy: 'bugReports')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: 'reportedBugReports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reporter = null;

    #[ORM\ManyToOne(inversedBy: 'assignedBugReports')]
    private ?User $assignedDeveloper = null;

    /**
     * @var Collection<int, BugComment>
     */
    #[ORM\OneToMany(mappedBy: 'bugReport', targetEntity: BugComment::class, orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $comments;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->comments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?? 'Bug report';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStepsToReproduce(): ?string
    {
        return $this->stepsToReproduce;
    }

    public function setStepsToReproduce(?string $stepsToReproduce): static
    {
        $this->stepsToReproduce = $stepsToReproduce;

        return $this;
    }

    public function getExpectedResult(): ?string
    {
        return $this->expectedResult;
    }

    public function setExpectedResult(?string $expectedResult): static
    {
        $this->expectedResult = $expectedResult;

        return $this;
    }

    public function getActualResult(): ?string
    {
        return $this->actualResult;
    }

    public function setActualResult(?string $actualResult): static
    {
        $this->actualResult = $actualResult;

        return $this;
    }

    public function getPriority(): BugPriority
    {
        return $this->priority;
    }

    public function setPriority(BugPriority $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getStatus(): BugStatus
    {
        return $this->status;
    }

    public function setStatus(BugStatus $status): static
    {
        $wasClosed = in_array($this->status, [BugStatus::Closed, BugStatus::Rejected], true);
        $willBeClosed = in_array($status, [BugStatus::Closed, BugStatus::Rejected], true);

        $this->status = $status;

        if ($willBeClosed && !$wasClosed) {
            $this->closedAt = new \DateTimeImmutable();
        } elseif (!$willBeClosed) {
            $this->closedAt = null;
        }

        return $this->touch();
    }

    public function getScreenshotFilename(): ?string
    {
        return $this->screenshotFilename;
    }

    public function setScreenshotFilename(?string $screenshotFilename): static
    {
        $this->screenshotFilename = $screenshotFilename;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getOpenedAt(): ?\DateTimeImmutable
    {
        return $this->openedAt;
    }

    public function setOpenedAt(?\DateTimeImmutable $openedAt): static
    {
        $this->openedAt = $openedAt;

        return $this;
    }

    public function markOpened(): static
    {
        if ($this->openedAt === null) {
            $this->openedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getTreatedAt(): ?\DateTimeImmutable
    {
        return $this->treatedAt;
    }

    public function setTreatedAt(?\DateTimeImmutable $treatedAt): static
    {
        $this->treatedAt = $treatedAt;

        return $this;
    }

    public function markTreated(): static
    {
        if ($this->treatedAt === null) {
            $this->treatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getReporter(): ?User
    {
        return $this->reporter;
    }

    public function setReporter(?User $reporter): static
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function getAssignedDeveloper(): ?User
    {
        return $this->assignedDeveloper;
    }

    public function setAssignedDeveloper(?User $assignedDeveloper): static
    {
        $this->assignedDeveloper = $assignedDeveloper;

        return $this;
    }

    /**
     * @return Collection<int, BugComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function touch(): static
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
