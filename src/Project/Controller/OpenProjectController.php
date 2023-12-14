<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Platform\PlatformClientInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class OpenProjectController
{
    /**
     * @var EntityRepository
     */
    protected $projectRepository;

    /**
     * @var PlatformClientInterface
     */
    private $platformClient;

    /**
     * @var string
     */
    private $widgetUrl;

    public function __construct(
        EntityRepository $projectRepository,
        PlatformClientInterface $platformClient,
        string $widgetUrl
    ) {
        $this->projectRepository = $projectRepository;
        $this->platformClient = $platformClient;
        $this->widgetUrl = $widgetUrl;
    }

    public function openProject(Request $request, string $uuid): RedirectResponse
    {
        $tokenString = $request->get('idToken');
        if (!$this->platformClient->validateToken($tokenString)) {
            throw new \Exception('Invalid token');
        }

        /** @var Project $project */
        $project = $this->projectRepository->findOneBy(['platformUuid' => $uuid]);
        if ($project === null) {
            throw new \Exception('Project not found with platformUuid ' . $uuid . ' not found');
        }

        return new RedirectResponse($this->widgetUrl . '/project/' . $project->getId());
    }
}
