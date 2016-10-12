<?php

namespace CultuurNet\ProjectAanvraag\Core\Schema;

use Doctrine\DBAL\Schema\AbstractSchemaManager;

interface SchemaConfiguratorInterface
{
    public function configure(AbstractSchemaManager $schemaManager);
}
