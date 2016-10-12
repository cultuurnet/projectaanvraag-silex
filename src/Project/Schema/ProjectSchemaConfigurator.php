<?php

namespace CultuurNet\ProjectAanvraag\Project\Schema;

use CultuurNet\ProjectAanvraag\Core\Schema\SchemaConfiguratorInterface;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

/**
 * Provides the schema configurator for projects.
 */
class ProjectSchemaConfigurator implements SchemaConfiguratorInterface
{

    const PROJECT_TABLE = 'projects';
    const PROJECT_ID_COLUMN = 'id';
    const LIVE_API_KEY_COLUMN = 'live_key';
    const TEST_API_KEY_COLUMN = 'test_key';

    /**
     * @inheritdoc
     */
    public function configure(AbstractSchemaManager $schemaManager)
    {
        $schema = $schemaManager->createSchema();

        if (!$schema->hasTable(self::PROJECT_TABLE)) {
            $table = $this->createTable($schema);
            $schemaManager->createTable($table);
        }
    }

    /**
     * @param Schema $schema
     * @return \Doctrine\DBAL\Schema\Table
     */
    private function createTable(Schema $schema)
    {
        $table = $schema->createTable(self::PROJECT_TABLE);

        $table->addColumn(self::PROJECT_ID_COLUMN, Type::BIGINT)
            ->setNotnull(true)
            ->setDefault(0);

        $table->addColumn(self::LIVE_API_KEY_COLUMN, Type::GUID)
            ->setLength(36);

        $table->addColumn(self::TEST_API_KEY_COLUMN, Type::GUID)
            ->setLength(36);

        $table->setPrimaryKey([self::PROJECT_ID_COLUMN]);
        $table->addUniqueIndex([self::LIVE_API_KEY_COLUMN]);
        $table->addUniqueIndex([self::TEST_API_KEY_COLUMN]);

        return $table;
    }
}
