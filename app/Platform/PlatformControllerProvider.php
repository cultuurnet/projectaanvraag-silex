<?php

namespace CultuurNet\ProjectAanvraag\Platform;

use CultuurNet\ProjectAanvraag\Platform\Controller\PlatformController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class PlatformControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['platform_controller'] = function (Application $app) {
            return new PlatformController($app['uitid_user_session_service']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $controllers->get('/logout/', 'platform_controller:logout');

        return $controllers;
    }
}
