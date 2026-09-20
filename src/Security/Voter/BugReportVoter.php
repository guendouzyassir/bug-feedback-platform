<?php

namespace App\Security\Voter;

use App\Entity\BugReport;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class BugReportVoter extends Voter
{
    public const VIEW = 'BUG_VIEW';
    public const MANAGE = 'BUG_MANAGE';
    public const UPDATE_STATUS = 'BUG_UPDATE_STATUS';
    public const DELETE = 'BUG_DELETE';
    public const CREATE = 'BUG_CREATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::MANAGE, self::UPDATE_STATUS, self::DELETE, self::CREATE], true)
            && ($subject instanceof BugReport || $subject === null);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::MANAGE => false,
            self::UPDATE_STATUS => $this->canUpdateStatus($subject, $user),
            self::DELETE => false,
            self::CREATE => $this->canCreate($user),
            default => false,
        };
    }

    private function canView(?BugReport $bug, User $user): bool
    {
        if ($bug === null) {
            return false;
        }

        if ($user->isDeveloper()) {
            return $bug->getProject()?->isDeveloperAssigned($user) ?? false;
        }

        if ($user->isClient()) {
            $project = $bug->getProject();

            if ($project === null) {
                return false;
            }

            return $project->isClientAssigned($user);
        }

        return false;
    }

    private function canUpdateStatus(?BugReport $bug, User $user): bool
    {
        if ($bug === null) {
            return false;
        }

        if ($user->isDeveloper()) {
            return $bug->getProject()?->isDeveloperAssigned($user) ?? false;
        }

        return false;
    }

    private function canCreate(User $user): bool
    {
        if ($user->isDeveloper()) {
            return $user->getDevelopmentProjects()->exists(fn ($key, $project) => $project->isActive());
        }

        if ($user->isClient()) {
            return $user->getAssignedProjects()->count() > 0;
        }

        return false;
    }
}
