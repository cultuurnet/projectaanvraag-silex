<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\UiTIDProvider\User\User as UiTIDUser;

class User extends UiTIDUser
{
    /**
     * @var array
     */
    protected $roles;

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles) {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
