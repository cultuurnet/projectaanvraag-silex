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
    public function connect(Application $app)
    {

        $app['widget_builder_api_controller'] = function (Application $app) {
            return new WidgetApiController($app['command_bus'], $app['widget_repository'], $app['widget_type_discovery'], $app['widget_page_deserializer'], $app['widget_page_converter'], $app['security.authorization_checker'], $app['widget_renderer'], $app['css_stats']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        // Get the widget types.
        $controllers->get('api/widget-types', 'widget_builder_api_controller:getWidgetTypes');

        // CRUD actions.
        $controllers->put('api/project/{project}/widget-page', 'widget_builder_api_controller:updateWidgetPage')
            ->convert('project', 'project_converter:convert');
        $controllers->post('api/project/{project}/widget-page/{widgetPage}/publish', 'widget_builder_api_controller:publishWidgetPage')
            ->convert('widgetPage', 'widget_page_converter:convertToDraft')
            ->convert('project', 'project_converter:convert');
        $controllers->post('api/project/{project}/widget-page/{widgetPage}/revert', 'widget_builder_api_controller:revertWidgetPage')
            ->convert('widgetPage', 'widget_page_converter:convertToDraft')
            ->convert('project', 'project_converter:convert');
        $controllers->delete('/api/project/{project}/widget-page/{widgetPage}', 'widget_builder_api_controller:deleteWidgetPage')
            ->convert('project', 'project_converter:convert')
            ->convert('widgetPage', 'widget_page_converter:convert');
        $controllers->post('api/project/{project}/widget-page/{widgetPage}/upgrade', 'widget_builder_api_controller:upgradeWidgetPage')
            ->convert('project', 'project_converter:convert')
            ->convert('widgetPage', 'widget_page_converter:convertToDraft');

        // Retrieve widget pages.
        $controllers->get('/api/project/{project}/widget-page', 'widget_builder_api_controller:getWidgetPages')
            ->convert('project', 'project_converter:convert');
        $controllers->get('/api/project/{project}/widget-page/{widgetPage}', 'widget_builder_api_controller:getWidgetPage')
            ->convert('project', 'project_converter:convert')
            ->convert('widgetPage', 'widget_page_converter:convertToDraft');

        // Get CSS stats.
        $controllers->get('api/css-stats', 'widget_builder_api_controller:getCssStats');

        return $controllers;
    }
}
