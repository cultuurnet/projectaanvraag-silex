<?php

namespace CultuurNet\UiTIDProvider\Auth;

use CultuurNet\Auth\ServiceInterface;
use CultuurNet\Auth\TokenCredentials;

interface AuthServiceInterface extends ServiceInterface
{
    /**
     * @param TokenCredentials $token
     */
    public function storeRequestToken(TokenCredentials $token);

    /**
     * @return TokenCredentials|null
     */
    public function getStoredRequestToken();

    public function removeStoredRequestToken();
}
