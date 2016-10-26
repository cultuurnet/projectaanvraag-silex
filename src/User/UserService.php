<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\UiTIDProvider\User\UserService as UiTIDUserService;

class UserService extends UiTIDUserService
{
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
        parent::__construct($cultureFeed);

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
}
