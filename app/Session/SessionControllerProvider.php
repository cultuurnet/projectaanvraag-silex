<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Session;

use CultuurNet\ProjectAanvraag\Session\Controller\SessionController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

final class SessionControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['session_controller'] = function (Application $app) {
            return new SessionController(
                $app['uitid_user_session_service'],
                'http://host.docker.internal:81/api/validateUser'
                // 'http://localhost:81/api/validateUser'
            );
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $controllers->get('/', 'session_controller:createSession');

        return $controllers;
    }
}
