<?php

namespace CultuurNet\UiTIDProvider\User;

class CachedUserService implements UserServiceInterface
{
    /**
     * @var UserServiceInterface
     */
    protected $userService;

    /**
     * @var User[]
     */
    protected $userCache;

    /**
     * @var array
     */
    protected $usernameToIdMap;

    /**
     * @param UserServiceInterface $userService
     */
    public function __construct(
        UserServiceInterface $userService
    ) {
        $this->userService = $userService;
        $this->userCache = array();
        $this->usernameToIdMap = array();
    }

    /**
     * @param User $user
     */
    public function cacheUser(User $user)
    {
        $this->userCache[$user->id] = $user;
        $this->usernameToIdMap[$user->nick] = $user->id;
    }

    /**
     * @param string $id
     * @return User|null
     */
    private function getCachedUserById($id)
    {
        if (isset($this->userCache[$id])) {
            return $this->userCache[$id];
        }
        return null;
    }

    /**
     * @param string $username
     * @return User|null
     */
    private function getCachedUserByUsername($username)
    {
        if (isset($this->usernameToIdMap[$username])) {
            return $this->getCachedUserById(
                $this->usernameToIdMap[$username]
            );
        }
        return null;
    }

    /**
     * @param string $id
     * @return User|null
     */
    public function getUser($id)
    {
        $cached = $this->getCachedUserById($id);
        if ($cached) {
            return $cached;
        }

        $user = $this->userService->getUser($id);

        if ($user) {
            $this->cacheUser($user);
        }

        return $user;
    }

    /**
     * @param $username
     * @return User|null
     */
    public function getUserByUsername($username)
    {
        $cached = $this->getCachedUserByUsername($username);
        if ($cached) {
            return $cached;
        }

        $user = $this->userService->getUserByUsername($username);

        if ($user) {
            $this->cacheUser($user);
        }

        return $user;
    }
}
