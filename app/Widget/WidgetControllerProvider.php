<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\IntegrationType\Controller\IntegrationTypeController;
use CultuurNet\ProjectAanvraag\Widget\Controller\WidgetApiController;
use CultuurNet\ProjectAanvraag\Widget\Controller\WidgetController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class WidgetControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {

        $app['widget_controller'] = function (Application $app) {
            return new WidgetController($app['widget_renderer'], $app['widget_repository'], $app['mongodb'], $app['search_api'], $app['widget_page_deserializer'], $app['debug']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/layout/{widgetpage}.js', 'widget_controller:renderPage')
            ->convert('widgetpage', 'widget_page_convertor:convert');

        $controllers->get('/search', 'widget_controller:searchExample');

        return $controllers;
    }
}
