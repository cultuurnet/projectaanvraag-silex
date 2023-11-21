<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

final class OpenProjectController
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var string
     */
    private $platformUrl;

    public function __construct(Session $session, string $platformUrl)
    {
        $this->session = $session;
        $this->platformUrl = $platformUrl;
    }

    public function openProject(Request $request, string $id): RedirectResponse
    {
        $tokenString = $request->get('idToken');
        $client = new Client();
        $request = $client->get($this->platformUrl . $tokenString);
        $response = $request->send();

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Invalid token');
        }

        $this->session->set('id_token', $tokenString);
        return new RedirectResponse('http://host.docker.internal:4200/project/' . $id);
    }
}
