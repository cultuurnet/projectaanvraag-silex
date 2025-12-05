<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\ProjectAanvraag\Platform\PlatformClientInterface;
use CultuurNet\UiTIDProvider\User\UserService as UiTIDUserService;
use Exception;

class UserService extends UiTIDUserService
{
    /**
     * @var UserRoleStorageInterface
     */
    protected $userRoleStorage;

    /**
     * @var PlatformClientInterface
     */
    private $platformClient;

    public function __construct(
        UserRoleStorageInterface $userRoleStorage,
        PlatformClientInterface $platformClient
    ) {
        $this->userRoleStorage = $userRoleStorage;
        $this->platformClient = $platformClient;
    }

    /**
     * @param string $id
     * @return User|null
     */
    public function getUser($id)
    {
        try {
            $userFromPlatform = $this->platformClient->getCurrentUser();
            if (empty($userFromPlatform)) {
                return null;
            }

            $user = User::fromPlatformUser(
                $userFromPlatform['sub'],
                $userFromPlatform['nickname']
            );

            // Add roles (always add uitid_user role)
            $roles = ['uitid_user'];
            $user->setRoles(array_merge($this->userRoleStorage->getRolesByUserId($user->id), $roles));

            return $user;
        } catch (Exception $e) {
            return null;
        }
    }
}
