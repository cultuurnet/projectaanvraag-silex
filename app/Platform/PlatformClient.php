<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Platform;

use CultuurNet\Auth\TokenCredentials;
use CultuurNet\Auth\User;
use CultuurNet\UiTIDProvider\User\UserSessionService;
use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Session\Session;

final class PlatformClient implements PlatformClientInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var UserSessionService
     */
    private $userSessionService;

    /**
     * @var Client
     */
    private $client;

    public function __construct(string $platformUrl, Session $session, UserSessionService $userSessionService)
    {
        $this->userSessionService = $userSessionService;
        $this->client = new Client($platformUrl, ['request.options' => ['exceptions' => false]]);
        $this->session = $session;
    }

    public function hasAccessOnIntegration(string $integrationId): bool
    {
        $idToken = $this->session->get('id_token');
        $request = $this->client->get('/api/token/' . $idToken . '/integration/' . $integrationId);
        $response = $request->send();

        if ($response->getStatusCode() === 200) {
            return true;
        }

        return false;
    }

    public function validateToken(string $idToken): bool
    {
        $request = $this->client->get('/api/token/' . $idToken);
        $response = $request->send();

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $this->userSessionService->setMinimalUserInfo(
            new User(
                $idToken,
                new TokenCredentials('token', 'secret')
            )
        );

        $this->session->set('id_token', $idToken);

        return true;
    }

    public function getCurrentUser(): array
    {
        $idToken = $this->session->get('id_token');

        $request = $this->client->get('/api/token/' . $idToken);
        $response = $request->send();

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return json_decode($response->getBody(true), true);
    }
}
