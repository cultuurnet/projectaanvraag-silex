<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\Auth\TokenCredentials;
use CultuurNet\Auth\User;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\UiTIDProvider\User\UserSessionService;
use Doctrine\ORM\EntityRepository;
use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

final class OpenProjectController
{
    /**
     * @var UserSessionService
     */
    private $userSessionService;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var EntityRepository
     */
    protected $projectRepository;

    /**
     * @var string
     */
    private $platformUrl;

    /**
     * @var string
     */
    private $widgetUrl;

    public function __construct(
        UserSessionService $userSessionService,
        Session $session,
        EntityRepository $projectRepository,
        string $platformUrl,
        string $widgetUrl
    ) {
        $this->userSessionService = $userSessionService;
        $this->session = $session;
        $this->projectRepository = $projectRepository;
        $this->platformUrl = $platformUrl;
        $this->widgetUrl = $widgetUrl;
    }

    public function openProject(Request $request, string $id): RedirectResponse
    {
        $tokenString = $request->get('idToken');

        $client = new Client();
        $request = $client->get($this->platformUrl . '/api/token/' . $tokenString);
        $response = $request->send();

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Invalid token');
        }

        $this->userSessionService->setMinimalUserInfo(
            new User(
                $tokenString,
                new TokenCredentials('token', 'secret')
            )
        );

        /** @var Project $project */
        $project = $this->projectRepository->findOneBy(['platformUuid' => $id]);

        $this->session->set('id_token', $tokenString);
        return new RedirectResponse($this->widgetUrl . '/project/' . $project->getId());
    }
}
