<?php

namespace CultuurNet\ProjectAanvraag\IntegrationTypes;

use CultuurNet\ProjectAanvraag\IntegrationTypes\Controller\IntegrationTypesController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class IntegrationTypesControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['integration_types_controller'] = function (Application $app) {
            return new IntegrationTypesController($app['integration_types.storage']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $controllers->get('/', 'integration_types_controller:listing');

        return $controllers;
    }
}
