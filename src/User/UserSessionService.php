<?php

namespace CultuurNet\UiTIDProvider\User;

use CultuurNet\Auth\User as MinimalUserInfo;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserSessionService implements UserSessionServiceInterface
{
    /**
     * Name of the session variable that stores the user's minimal info.
     */
    const MINIMAL_USER_SESSION_VARIABLE = 'culturefeed_minimal_user';

    /**
     * Name of the session variable that stores the user.
     */
    const USER_SESSION_VARIABLE = 'culturefeed_user';

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param MinimalUserInfo $user
     */
    public function setMinimalUserInfo(MinimalUserInfo $user)
    {
        $this->session->set(self::MINIMAL_USER_SESSION_VARIABLE, $user);
    }

    /**
     * @return MinimalUserInfo|null
     */
    public function getMinimalUserInfo()
    {
        return $this->session->get(self::MINIMAL_USER_SESSION_VARIABLE);
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->session->set(self::USER_SESSION_VARIABLE, $user);
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->session->get(self::USER_SESSION_VARIABLE);
    }

    public function logout()
    {
        $this->session->invalidate();
    }
}
