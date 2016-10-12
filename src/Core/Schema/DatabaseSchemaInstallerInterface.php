<?php

namespace CultuurNet\ProjectAanvraag\Core\Schema;


interface DatabaseSchemaInstallerInterface
{

    /**
     * Add a schema configurator to the list of schemas to configure.
     * @param SchemaConfiguratorInterface $schemaConfigurator
     */
    public function addSchemaConfigurator(SchemaConfiguratorInterface $schemaConfigurator);

    /**
     * Install the schema.
     */
    public function installSchema();

}