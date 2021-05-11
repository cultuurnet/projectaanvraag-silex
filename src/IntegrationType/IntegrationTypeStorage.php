<?php

namespace CultuurNet\ProjectAanvraag\IntegrationType;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\IntegrationType as InsightlyIntegrationType;
use Symfony\Component\Yaml\Yaml;

class IntegrationTypeStorage implements IntegrationTypeStorageInterface
{
    /**
     * @var string
     */
    protected $configFilePath;

    /**
     * @var array
     */
    protected $integrationTypes;

    /**
     * IntegrationTypesStorage constructor.
     * @param string $configFilePath
     */
    public function __construct($configFilePath)
    {
        $this->configFilePath = $configFilePath;
    }

    public function getIntegrationTypes()
    {
        if (!is_array($this->integrationTypes)) {
            // Load the integration types when fetching
            // This prevents the YAML from being read when the storage gets initialized
            $this->loadIntegrationTypes();
        }

        return $this->integrationTypes;
    }

    public function load($id)
    {
        $types = $this->getIntegrationTypes();
        return !empty($types[$id]) ? $types[$id] : null;
    }

    /**
     * Load the integration types from the YAML config file
     */
    private function loadIntegrationTypes()
    {
        $this->integrationTypes = [];

        $types = Yaml::parse(file_get_contents($this->configFilePath));
        if (is_array($types) && !empty($types['integration_types'])) {
            foreach ($types['integration_types'] as $id => $type) {
                $integrationType = new IntegrationType();
                $integrationType->setId($id);
                $integrationType->setName(!empty($type['name']) ? $type['name'] : null);
                $integrationType->setDescription(!empty($type['description']) ? $type['description'] : null);
                $integrationType->setExtraInfo(!empty($type['extra_info']) ? $type['extra_info'] : null);
                $integrationType->setGroupId(!empty($type['group_id']) ? $type['group_id'] : null);
                $integrationType->setPrice(!empty($type['price']) ? $type['price'] : null);
                $integrationType->setUrl(!empty($type['url']) ? $type['url'] : null);
                $integrationType->setGetStartedUrl(!empty($type['get_started_url']) ? $type['get_started_url'] : null);
                $integrationType->setActionButton(!empty($type['action_button']) ? $type['action_button'] : '');
                $integrationType->setSapiVersion(!empty($type['sapi_version']) ? $type['sapi_version'] : '2');
                $integrationType->setSelfService(isset($type['self_service']) ? $type['self_service'] : true);
                $integrationType->setEnableActivation(isset($type['enable_activation']) ? $type['enable_activation'] : true);
                $integrationType->setType(!empty($type['type']) ? $type['type'] : 'output');
                $integrationType->setUitIdPermissionGroups($type['uitid_permissions'] ?? []);
                $integrationType->setUitPasPermissionGroups($type['uitpas_permissions'] ?? []);

                if (!empty($type['insightly_integration_type'])) {
                    $integrationType->setInsightlyIntegrationType(InsightlyIntegrationType::fromKey($type['insightly_integration_type']));
                }

                $this->integrationTypes[$integrationType->getId()] = $integrationType;
            }
        }
    }
}
