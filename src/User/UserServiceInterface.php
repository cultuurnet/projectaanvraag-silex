<?php

namespace CultuurNet\ProjectAanvraag\User;

interface UserServiceInterface
{
    /**
     * @param string $id
     * @return User
     */
    public function getUser($id);

    /**
     * @param $username
     * @return User|null
     */
    public function getUserByUsername($username);
}
