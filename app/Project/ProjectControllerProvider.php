<?php

namespace CultuurNet\ProjectAanvraag\Project;

use CultuurNet\ProjectAanvraag\Project\Controller\ProjectController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

/**
 * @file
 */
class ProjectControllerProvider implements ControllerProviderInterface
{

    public function connect(Application $app)
    {

        $app['project_controller'] = function (Application $app) {
            return new ProjectController($app['command_bus'], $app['integration_types.storage']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $controllers->get('/', 'project_controller:listing');
        $controllers->get('/test', 'project_controller:listing');

        return $controllers;
    }
}
