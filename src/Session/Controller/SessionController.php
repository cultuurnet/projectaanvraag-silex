<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Session\Controller;

use CultuurNet\Auth\Session;
use CultuurNet\Auth\TokenCredentials;
use CultuurNet\Auth\User;
use CultuurNet\UiTIDProvider\User\UserSessionServiceInterface;
use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class SessionController
{
    /**
     * @var UserSessionServiceInterface
     */
    private $userSessionService;

    /*
     * @var string
     */
    private $platformUrl;

    /**
     * @param UserSessionServiceInterface $userSessionService
     */
    public function __construct(UserSessionServiceInterface $userSessionService, string $platformUrl)
    {
        $this->userSessionService = $userSessionService;
        $this->platformUrl = $platformUrl;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createSession(Request $request): RedirectResponse
    {
        // Get the JWT
        $tokenString = $request->get('idToken');
        $client = new Client();
        $request = $client->get($this->platformUrl, null, ['query' => ['idToken' => $tokenString]]);
        $response = $request->send();
        $userFromPlatform = json_decode($response->getBody(true), true);

        // Create a session

        $this->userSessionService->setMinimalUserInfo(

            new User(
                $tokenString,
                new TokenCredentials('idToken', $tokenString)
                //$userFromPlatform['id'],
                //new TokenCredentials($userFromPlatform['token'], $userFromPlatform['secret'])
            )
        );
        // Redirect to widget builder
        return new RedirectResponse('http://localhost:9999');
    }
}
