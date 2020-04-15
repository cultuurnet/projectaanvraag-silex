<?php

namespace CultuurNet\UiTIDProvider\User;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class UserControllerProvider implements ControllerProviderInterface
{
    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        $app['uitid_user_controller'] = function (Application $app) {
            return new UserController(
                $app['uitid_user_service'],
                $app['uitid_user_session_service']
            );
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/user', 'uitid_user_controller:getUser');
        $controllers->get('/logout', 'uitid_user_controller:logout');

        return $controllers;
    }
}
