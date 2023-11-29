<?php

namespace CultuurNet\ProjectAanvraag\Voter;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Platform\PlatformClientInterface;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProjectVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const ACTIVATE = 'activate';
    const BLOCK = 'block';

    /**
     * @var PlatformClientInterface
     */
    private $platformClient;

    private $checkAccessOnPlatform;

    public function __construct(
        PlatformClientInterface $platformClient,
        bool $checkAccessOnPlatform
    ) {
        $this->platformClient = $platformClient;
        $this->checkAccessOnPlatform = $checkAccessOnPlatform;
    }

    /**
     * @param string $attribute
     * @param ProjectInterface $project
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $project, TokenInterface $token)
    {
        /** @var UserInterface $user */
        $user = $token->getUser();

        // Allow administrators to perform all operations
        if ($user->hasRole(User::USER_ROLE_ADMINISTRATOR)) {
            return true;
        }

        // Allow users to only view and edit their own projects
        $hasAccess = (self::EDIT === $attribute || self::VIEW === $attribute) && $project->getUserId() === $user->id;

        if (!$hasAccess && $this->checkAccessOnPlatform && $project->getPlatformUuid() !== null) {
            $hasAccess = $this->platformClient->hasAccessOnIntegration($project->getPlatformUuid());
        }

        return $hasAccess;
    }

    /**
     * @param string $attribute
     * @param mixed $object
     * @return bool
     */
    protected function supports($attribute, $object)
    {
        return $object instanceof ProjectInterface && in_array($attribute, [self::VIEW, self::EDIT, self::BLOCK, self::ACTIVATE]);
    }
}
