<?php

namespace CultuurNet\UiTIDProvider\User;

use CultuurNet\Auth\User as MinimalUserInfo;

interface UserSessionServiceInterface
{
    /**
     * @param MinimalUserInfo $user
     */
    public function setMinimalUserInfo(MinimalUserInfo $user);

    /**
     * @return MinimalUserInfo|null
     */
    public function getMinimalUserInfo();

    public function logout();
}
