<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\UiTIDProvider\User\UserServiceInterface;

class UserService implements UserServiceInterface
{
    /**
     * Include private fields when returning user data.
     */
    const INCLUDE_PRIVATE_FIELDS = true;

    /**
     * @var \CultureFeed
     */
    protected $cultureFeed;

    /**
     * @var UserRoleStorageInterface
     */
    protected $userRoleStorage;

    /**
     * @param \CultureFeed $cultureFeed
     * @param UserRoleStorageInterface $userRoleStorage
     */
    public function __construct(\CultureFeed $cultureFeed, UserRoleStorageInterface $userRoleStorage)
    {
        $this->cultureFeed = $cultureFeed;
        $this->userRoleStorage = $userRoleStorage;
    }

    /**
     * @param string $id
     * @return User|null
     */
    public function getUser($id)
    {
        try {
            $cfUser = $this->cultureFeed->getUser($id, self::INCLUDE_PRIVATE_FIELDS);

            // Cast to a User object that can be safely encoded to json and add the user roles.
            $user = User::fromCultureFeedUser($cfUser);

            // Add roles (always add uitid_user role)
            $roles = ['uitid_user'];

            $user->setRoles(array_merge($this->userRoleStorage->getRolesByUserId($user->id), $roles));

            return $user;
        } catch (\CultureFeed_ParseException $e) {
            return null;
        }
    }

    /**
     * @param $username
     * @return User|null
     */
    public function getUserByUsername($username)
    {
        try {
            $query = new \CultureFeed_SearchUsersQuery();
            $query->nick = $username;

            $results = $this->cultureFeed->searchUsers($query);
            $users = $results->objects;

            if (empty($users)) {
                return null;
            }

            $user = reset($users);

            return $this->getUser($user->id);
        } catch (\CultureFeed_ParseException $e) {
            return null;
        }
    }
}
