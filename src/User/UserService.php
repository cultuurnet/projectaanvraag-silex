<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\UiTIDProvider\User\UserService as UiTIDUserService;
use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Session\Session;

class UserService extends UiTIDUserService
{
    /**
     * @var UserRoleStorageInterface
     */
    protected $userRoleStorage;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var string
     */
    private $platformUrl;

    /*
     * @var Client
     */
    private $client;

    public function __construct(
        \CultureFeed $cultureFeed,
        UserRoleStorageInterface $userRoleStorage,
        Session $session,
        string $platformUrl,
        Client $client
    ) {
        parent::__construct($cultureFeed);
        $this->userRoleStorage = $userRoleStorage;
        $this->session = $session;
        $this->platformUrl = $platformUrl;
        $this->client = $client;
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
                // Cast to a User object that can be safely encoded to json and add the user roles.
                $user = User::fromCultureFeedUser($cfUser);
            } catch (\Exception $e) {
                $idToken = $this->session->get('id_token');

                $request = $this->client->get(
                    $this->platformUrl . '/api/token/' . $idToken
                );
                $response = $request->send();
                $userFromPlatform = json_decode($response->getBody(true), true);

                $user = User::fromPlatformUser(
                    $userFromPlatform['sub'],
                    $userFromPlatform['nickname']
                );
            }

            // Add roles (always add uitid_user role)
            $roles = ['uitid_user'];
            $user->setRoles(array_merge($this->userRoleStorage->getRolesByUserId($user->id), $roles));

            return $user;
        } catch (\CultureFeed_ParseException $e) {
            return null;
        }
    }
}
