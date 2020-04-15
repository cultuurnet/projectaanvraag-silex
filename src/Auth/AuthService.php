<?php

namespace CultuurNet\UiTIDProvider\Auth;

use CultuurNet\Auth\ConsumerCredentials;
use CultuurNet\Auth\Guzzle\Service;
use CultuurNet\Auth\TokenCredentials;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthService extends Service implements AuthServiceInterface
{
    /**
     * Name of the session variable that stores the request token.
     */
    const REQUEST_TOKEN_VARIABLE = 'culturefeed_request_token';

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @param string $baseUrl
     * @param ConsumerCredentials $consumerCredentials
     * @param SessionInterface $session
     */
    public function __construct($baseUrl, ConsumerCredentials $consumerCredentials, SessionInterface $session)
    {
        parent::__construct($baseUrl, $consumerCredentials);
        $this->session = $session;
    }

    /**
     * @param TokenCredentials $token
     */
    public function storeRequestToken(TokenCredentials $token)
    {
        $this->session->set(self::REQUEST_TOKEN_VARIABLE, $token);
    }

    /**
     * @return TokenCredentials|null
     */
    public function getStoredRequestToken()
    {
        return $this->session->get(self::REQUEST_TOKEN_VARIABLE);
    }

    public function removeStoredRequestToken()
    {
        $this->session->remove(self::REQUEST_TOKEN_VARIABLE);
    }
}
