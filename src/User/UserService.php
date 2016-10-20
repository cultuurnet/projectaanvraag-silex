<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\UiTIDProvider\User\UserService as UiTIDUserService;

class UserService extends UiTIDUserService
{
    /**
     * @param string $id
     * @return User|null
     */
    public function getUser($id)
    {
        if ($user = parent::getUser($id)) {
            // Add user roles

            return $user;
        }

        return null;
    }
}
