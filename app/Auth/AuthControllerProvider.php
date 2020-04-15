<?php

namespace CultuurNet\UiTIDProvider\Auth;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class AuthControllerProvider implements ControllerProviderInterface
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
        $app['uitid_auth_controller'] = function (Application $app) {
            return new AuthController(
                $app['uitid_auth_service'],
                $app['uitid_user_session_service'],
                $app['url_generator']
            );
        };

        $controllers = $app['controllers_factory'];

        $controllers->get('/connect', 'uitid_auth_controller:connect');
        $controllers->get('/authorize', 'uitid_auth_controller:authorize')
            ->bind(AuthController::AUTHORISATION_ROUTE_NAME);

        return $controllers;
    }
}
