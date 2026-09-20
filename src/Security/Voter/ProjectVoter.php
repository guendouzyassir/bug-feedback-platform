<?php

namespace App\Security\Voter;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectVoter extends Voter
{
    public const VIEW = 'PROJECT_VIEW';
    public const MANAGE = 'PROJECT_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::MANAGE], true)
            && $subject instanceof Project;
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
            default => false,
        };
    }

    private function canView(Project $project, User $user): bool
    {
        if ($user->isDeveloper()) {
            return $project->isDeveloperAssigned($user);
        }

        if ($user->isClient()) {
            return $project->isClientAssigned($user);
        }

        return false;
    }
}
