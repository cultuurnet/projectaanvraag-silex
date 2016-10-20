<?php

namespace CultuurNet\ProjectAanvraag\IntegrationType;

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

    /**
     * {@inheritdoc}
     */
    public function getIntegrationTypes()
    {
        if (!is_array($this->integrationTypes)) {
            // Load the integration types when fetching
            // This prevents the YAML from being read when the storage gets initialized
            $this->loadIntegrationTypes();
        }

        return $this->integrationTypes;
    }

    /**
     * {@inheritdoc}
     */
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
        if (!empty($types) && is_array($types) && !empty($types['integration_types'])) {
            foreach ($types['integration_types'] as $id => $type) {
                $integrationType = new IntegrationType();
                $integrationType->setId($id);
                $integrationType->setName(!empty($type['name']) ? $type['name'] : null);
                $integrationType->setDescription(!empty($type['description']) ? $type['description'] : null);
                $integrationType->setExtraInfo(!empty($type['extra_info']) ? $type['extra_info'] : null);
                $integrationType->setGroupId(!empty($type['group_id']) ? $type['group_id'] : null);
                $integrationType->setPrice(!empty($type['price']) ? $type['price'] : null);
                $integrationType->setUrl(!empty($type['url']) ? $type['url'] : null);

                $this->integrationTypes[$integrationType->getId()] = $integrationType;
            }
        }
    }
}
