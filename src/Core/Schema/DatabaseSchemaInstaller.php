<?php

namespace CultuurNet\ProjectAanvraag\Core\Schema;

use Doctrine\DBAL\Connection;
use Pimple\Container;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;

/**
 * Installs the registered database schemes.
 */
class DatabaseSchemaInstaller implements DatabaseSchemaInstallerInterface
{

    protected $container;

    /**
     * @var SchemaConfiguratorInterface[]
     */
    protected $schemaConfigurators;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->schemaConfigurators = [];
    }

    /**
     * Add a new schema configurator.
     */
    public function addSchemaConfigurator(SchemaConfiguratorInterface $schemaConfigurator) {
        $this->schemaConfigurators[] = $schemaConfigurator;
    }

    /**
     * Install the current schema.
     */
    public function installSchema()
    {
        $schemaManager = $this->getConnection()->getSchemaManager();
        foreach ($this->schemaConfigurators as $configurator) {
            $configurator->configure($schemaManager);
        }
    }

    /**
     * Uninstall the existing schema.
     */
    public function uninstallSchema()
    {
        $schemaManager = $this->getConnection()->getSchemaManager();
        $currentSchema = $schemaManager->createSchema();
        $existingTables = $currentSchema->getTableNames();

        foreach ($existingTables as $table) {
            $schemaManager->dropTable($table);
            $currentSchema->dropTable($table);
        }
    }

    /**
     * Get the current db connection.
     * @return Connection
     */
    private function getConnection()
    {

        if (!isset($this->container['db'])) {
            throw new ProviderNotFoundException('db not found');
        }

        return $this->container['db'];
    }
}