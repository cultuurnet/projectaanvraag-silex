<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\UiTIDProvider\User\UserService as UiTIDUserService;
use Guzzle\Http\Client;

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
            try {
                $cfUser = $this->cultureFeed->getUser($id, self::INCLUDE_PRIVATE_FIELDS);
                //var_dump($cfUser); exit;

                // Cast to a User object that can be safely encoded to json and add the user roles.
                $user = User::fromCultureFeedUser($cfUser);

                // Add roles (always add uitid_user role)
                $roles = ['uitid_user'];
                $user->setRoles(array_merge($this->userRoleStorage->getRolesByUserId($user->id), $roles));
            } catch (\Exception $e) {
                $client = new Client();
                $request = $client->get('http://host.docker.internal:81/api/user', null, ['query' => ['idToken' => $id]]);
                $response = $request->send();
                $userFromPlatform = json_decode($response->getBody(true), true);
                // Do a call to publiq-platform to get the user
                $user = User::fromPlatformUser($id, $userFromPlatform['nickname']);
            }

            return $user;
        } catch (\CultureFeed_ParseException $e) {
            return null;
        }
    }
}
