<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\IntegrationType\Controller\IntegrationTypeController;
use CultuurNet\ProjectAanvraag\Widget\Controller\WidgetController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class WidgetControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['widget_controller'] = function (Application $app) {
            $renderer = new Renderer();
            return new WidgetController($renderer, $app['mongodbodm.dm'], $app['mongodb']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $controllers->get('/', 'widget_controller:renderPage');

        return $controllers;
    }
}
