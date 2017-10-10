<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\Widget\Controller\WidgetApiController;
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
            return new WidgetApiController($app['command_bus'], $app['widget_repository'], $app['widget_type_discovery'], $app['widget_page_deserializer'], $app['security.authorization_checker']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        // Get the widget types.
        $controllers->get('api/widget-types', 'widget_builder_api_controller:getWidgetTypes');

        // CRUD actions.
        $controllers->put('api/project/{project}/widget-page', 'widget_builder_api_controller:updateWidgetPage')
            ->convert('project', 'project_converter:convert');
        $controllers->post('api/project/{project}/widget-page/{pageId}/publish', 'widget_builder_api_controller:publishWidgetPage')
            ->convert('project', 'project_converter:convert');
        $controllers->delete('/api/project/{project}/widget-page/{widgetPage}', 'widget_builder_api_controller:deleteWidgetPage')
            ->convert('project', 'project_converter:convert')
            ->convert('widgetPage', 'widget_page_convertor:convert');
        

        // Retrieve widget pages.
        $controllers->get('/api/project/{project}/widget-page', 'widget_builder_api_controller:getWidgetPages')
            ->convert('project', 'project_converter:convert');
        $controllers->get('/api/project/{project}/widget-page/{widgetPage}', 'widget_builder_api_controller:getWidgetPage')
            ->convert('project', 'project_converter:convert')
            ->convert('widgetPage', 'widget_page_convertor:convertToDraft');


        return $controllers;
    }
}
