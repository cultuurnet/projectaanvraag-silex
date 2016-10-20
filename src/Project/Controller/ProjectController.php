<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use CultuurNet\ProjectAanvraag\IntegrationTypes\IntegrationTypesStorageInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for project related tasks.
 */
class ProjectController
{
    protected $commandBus;
    protected $integrationTypesStorage;

    public function __construct(MessageBusSupportingMiddleware $commandBus, IntegrationTypeStorageInterface $integrationTypesStorage)
    {
        $this->commandBus = $commandBus;
        $this->integrationTypesStorage = $integrationTypesStorage;
    }

    public function listing()
    {
        $types = $this->integrationTypesStorage->getIntegrationTypes();

        return new JsonResponse($types);
    }
}
