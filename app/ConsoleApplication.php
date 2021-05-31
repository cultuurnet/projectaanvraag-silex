<?php

namespace CultuurNet\ProjectAanvraag;

use CultuurNet\ProjectAanvraag\Console\Command\CacheClearCommand;
use CultuurNet\ProjectAanvraag\Console\Command\ConsumeCommand;
use CultuurNet\ProjectAanvraag\Console\Command\MigrateInsightlyIds;
use CultuurNet\ProjectAanvraag\Console\Command\SyncConsumersCommand;
use CultuurNet\ProjectAanvraag\WidgetMigration\Console\Command\MigrateCommand;
use Doctrine\DBAL\Tools\Console\Command\ImportCommand;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand;
use Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand;
use Doctrine\ORM\Tools\Console\Command\RunDqlCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Knp\Provider\ConsoleServiceProvider;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Application class for the projectaanvraag app: console version.
 */
class ConsoleApplication extends ApplicationBase
{

    public function __construct()
    {
        parent::__construct();
        $this->registerCommands();
    }

    /**
     * Register all service providers.
     */
    protected function registerProviders()
    {
        parent::registerProviders();

        /**
         * Set culturefeed_token_credentials to null so we don't have to inject UserServiceProvider and SessionServiceProvider.
         * The console has no notion of a logged in user anyways.
        */
        $this['culturefeed_token_credentials'] = null;

        $this->register(
            new ConsoleServiceProvider(),
            [
               'console.name' => 'Projectaanvraag',
                'console.version' => '1.0.0',
                'console.project_directory' => __DIR__ . '/..',
            ]
        );
    }

    /**
     * Register all commands.
     */
    protected function registerCommands()
    {
        $consoleApp = $this['console'];

        $consoleApp->add(new CacheClearCommand());
        $consoleApp->add(new ConsumeCommand('projectaanvraag:consumer', 'rabbit.connection', 'rabbit.consumer'));

        // Sync culturefeed consumers with local DB
        $consoleApp->add(new SyncConsumersCommand());

        // Widget commands
        $consoleApp->add(new MigrateCommand());

        $consoleApp->add(new MigrateInsightlyIds());

        // Doctrine helperset
        $em = $this['orm.em'];
        $helperSet = new HelperSet(
            [
                'db' => new ConnectionHelper($em->getConnection()),
                'em' => new EntityManagerHelper($em),
            ]
        );

        $consoleApp->setHelperSet($helperSet);

        // Doctrine commands
        $consoleApp->addCommands(
            [
                // DBAL Commands
                new RunSqlCommand(),
                new ImportCommand(),
                // ORM Commands
                new MetadataCommand(),
                new ResultCommand(),
                new QueryCommand(),
                new CreateCommand(),
                new UpdateCommand(),
                new DropCommand(),
                new EnsureProductionSettingsCommand(),
                new ConvertDoctrine1SchemaCommand(),
                new GenerateRepositoriesCommand(),
                new GenerateEntitiesCommand(),
                new GenerateProxiesCommand(),
                new ConvertMappingCommand(),
                new RunDqlCommand(),
                new ValidateSchemaCommand(),
            ]
        );
    }
}
