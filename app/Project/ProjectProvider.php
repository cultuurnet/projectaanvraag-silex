<?php

namespace CultuurNet\ProjectAanvraag\Project;

use CultuurNet\ProjectAanvraag\Core\Schema\DatabaseSchemaInstaller;
use CultuurNet\ProjectAanvraag\Project\Schema\ProjectSchemaConfigurator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provides project related services.
 */
class ProjectProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register(Container $pimple)
    {
        $pimple['database.installer'] = $pimple->extend(
            'database.installer',
            function (DatabaseSchemaInstaller $installer, Container $pimple) {
                $installer->addSchemaConfigurator(
                    new ProjectSchemaConfigurator()
                );
                return $installer;
            }
        );

        $pimple['project_service'] = function (Container $pimple) {
            return new ProjectService($pimple['culturefeed'], $pimple['culturefeed_test']);
        };
    }
}
