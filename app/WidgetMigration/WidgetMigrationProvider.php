<?php

namespace CultuurNet\ProjectAanvraag\WidgetMigration;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class WidgetMigrationProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {

        /**
         * Provides a legacy database connection.
         */
        $pimple['legacy_widgets_db'] = function (Container $pimple) {
            return $pimple['dbs']['legacy_widgets'];
        };

        $pimple['widgets_migration_logger'] = function (Container $pimple) {
            $logger = new Logger('widgets-migration');
            $logger->pushHandler(new RotatingFileHandler(__DIR__ . '/../../log/widgets-migration/migration.log', 0, Logger::DEBUG));
            return $logger;
        };
    }
}
