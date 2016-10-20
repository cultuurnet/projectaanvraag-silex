<?php

namespace CultuurNet\ProjectAanvraag\User;

interface UserRoleStorageInterface
{
    /**
     * Gets a list of all user roles
     *
     * @return array
     */
    public function getRoles();

    /**
     * Gets a list of all user roles with the mapped user ids
     *
     * @return array
     */
    public function getRolesAndUserIds();

    /**
     * Get the user roles for a single user
     *
     * @param string $userId
     * @return array
     */
    public function getRolesByUserId($userId);
}
