<?php

namespace CultuurNet\ProjectAanvraag\IntegrationTypes\Controller;

use CultuurNet\ProjectAanvraag\IntegrationTypes\IntegrationTypesStorageInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for IntegrationType related tasks.
 */
class IntegrationTypesController
{
    /**
     * @var IntegrationTypesStorageInterface
     */
    protected $integrationTypesStorage;

    public function __construct(IntegrationTypesStorageInterface $integrationTypesStorage)
    {
        $this->integrationTypesStorage = $integrationTypesStorage;
    }

    public function listing()
    {
        return new JsonResponse($this->integrationTypesStorage->getIntegrationTypes());
    }
}
