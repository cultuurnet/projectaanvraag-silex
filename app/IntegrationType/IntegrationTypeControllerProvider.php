<?php

namespace CultuurNet\ProjectAanvraag\IntegrationType;

use CultuurNet\ProjectAanvraag\IntegrationType\Controller\IntegrationTypeController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class IntegrationTypeControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['integration_types_controller'] = function (Application $app) {
            return new IntegrationTypeController($app['integration_types.storage']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $controllers->get('/', 'integration_types_controller:listing');

        return $controllers;
    }
}
