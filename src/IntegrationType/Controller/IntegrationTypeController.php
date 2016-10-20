<?php

namespace CultuurNet\ProjectAanvraag\IntegrationType\Controller;

use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for IntegrationType related tasks.
 */
class IntegrationTypeController
{
    /**
     * @var IntegrationTypeStorageInterface
     */
    protected $integrationTypeStorage;

    public function __construct(IntegrationTypeStorageInterface $integrationTypeStorage)
    {
        $this->integrationTypeStorage = $integrationTypeStorage;
    }

    public function listing()
    {
        return new JsonResponse($this->integrationTypeStorage->getIntegrationTypes());
    }
}
