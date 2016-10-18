<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for project related tasks.
 */
class ProjectController
{

    protected $commandBus;
    protected $insightlyClient;

    public function __construct(MessageBusSupportingMiddleware $commandBus, InsightlyClientInterface $insightlyClient)
    {
        $this->commandBus = $commandBus;
        $this->insightlyClient = $insightlyClient;
    }

    public function listing()
    {
        return new JsonResponse($this->insightlyClient->getProjects());
    }
}
