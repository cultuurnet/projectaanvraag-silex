<?php

namespace CultuurNet\ProjectAanvraag\IntegrationType;

interface IntegrationTypeStorageInterface
{
    /**
     * Gets a list of integration types
     *
     * @return IntegrationType[]
     */
    public function getIntegrationTypes();

    /**
     * Load a single integration type
     *
     * @param string $id
     * @return IntegrationType|null
     */
    public function load($id);
}
