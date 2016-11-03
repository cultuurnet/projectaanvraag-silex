<?php

namespace CultuurNet\ProjectAanvraag\Voter;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProjectVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const ACTIVATE = 'activate';

    /**
     * @param string $attribute
     * @param Project $project
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $project, TokenInterface $token)
    {
        /** @var UserInterface $user */
        $user = $token->getUser();

        // Allow administrators to perform all operations
        if ($user->hasRole('administrator')) {
            return true;
        }

        // Allow users to only view and edit their own projects
        return (self::EDIT === $attribute || self::VIEW) && $project->getUserId() === $user->id;
    }

    /**
     * @param string $attribute
     * @param mixed $object
     * @return bool
     */
    protected function supports($attribute, $object)
    {
        return $object instanceof Project && in_array($attribute, [self::VIEW, self::EDIT, self::ACTIVATE]);
    }
}
