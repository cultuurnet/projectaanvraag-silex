<?php

namespace CultuurNet\ProjectAanvraag\User;

interface UserInterface extends \Symfony\Component\Security\Core\User\UserInterface
{
    /**
     * Set the roles of current user.
     * @return UserInterface
     */
    public function setRoles(array $roles);

    /**
     * Checks if the User has a given role
     * @param string $role
     * @return bool
     */
    public function hasRole($role);

    /**
     * Check if the current user is admin.
     * @return bool
     */
    public function isAdmin();
}
