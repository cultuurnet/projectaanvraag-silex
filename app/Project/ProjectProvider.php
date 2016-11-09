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

        $pimple['project_repository'] = function (Container $pimple) {
            return $pimple['orm.em']->getRepository('ProjectAanvraag:Project');
        };

        $pimple['project_service'] = function (Container $pimple) {
            return new ProjectService($pimple['culturefeed'], $pimple['culturefeed_test'], $pimple['project_repository'], $pimple['integration_types.storage'], $pimple['uitid_user']);
        };
    }
}
