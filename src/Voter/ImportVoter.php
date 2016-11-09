<?php

namespace CultuurNet\ProjectAanvraag\Voter;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ImportVoter extends Voter
{
    const IMPORT = 'import';

    /**
     * @param string $attribute
     * @param ProjectInterface $project
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        /** @var UserInterface $user */
        $user = $token->getUser();

        // Allow administrators to perform imports.
        return ($user->hasRole(User::USER_ROLE_ADMINISTRATOR));
    }

    /**
     * @param string $attribute
     * @param mixed $object
     * @return bool
     */
    protected function supports($attribute, $object)
    {
        return $attribute == self::IMPORT && $object === null;
    }
}
