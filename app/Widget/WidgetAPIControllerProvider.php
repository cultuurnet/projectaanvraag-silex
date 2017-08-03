<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\IntegrationType\Controller\IntegrationTypeController;
use CultuurNet\ProjectAanvraag\Widget\Controller\WidgetApiController;
use CultuurNet\ProjectAanvraag\Widget\Controller\WidgetController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

/**
 * Provides a provider for widget API requests.
 */
class WidgetAPIControllerProvider implements ControllerProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {

        $app['widget_builder_api_controller'] = function (Application $app) {
            return new WidgetApiController($app['widget_repository'], $app['widget_type_discovery'], $app['widget_page_deserializer'], $app['security.authorization_checker']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $controllers->get('api/widget-types', 'widget_builder_api_controller:getWidgetTypes');
        $controllers->put('api/project/{project}/widget-page', 'widget_builder_api_controller:updateWidgetPage')
            ->convert('project', 'project_converter:convert');
        $controllers->put('api/test', 'widget_builder_api_controller:test');
        $controllers->post('api/test', 'widget_builder_api_controller:test');
        $controllers->get('api/test', 'widget_builder_api_controller:test');

        return $controllers;
    }
}
