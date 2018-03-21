<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\ProjectAanvraag\Core\Controller\HomeController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class HomeControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {

        $app['home_controller'] = function (Application $app) {
            return new HomeController($app['config']['app_host']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'home_controller:index');

        return $controllers;
    }
}
