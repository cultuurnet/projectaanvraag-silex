<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\Widget\Controller\WidgetController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class WidgetControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {

        $app['widget_controller'] = function (Application $app) {
            return new WidgetController($app['widget_renderer'], $app['widget_repository'], $app['project_converter'], $app['mongodb'], $app['widget_page_deserializer'], $app['debug'], $app['config']['legacy_host'], $app['widget_region_service']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/layout/{widgetPage}.js', 'widget_controller:renderPage')
            ->convert('widgetPage', 'widget_page_converter:convert');
        $controllers->get('/layout/v3/{widgetPage}.js', 'widget_controller:renderPageForceCurrent')
            ->convert('widgetPage', 'widget_page_converter:convert');

        // Render widgets.
        $controllers->get('/api/render/{widgetPage}/{widgetId}', 'widget_controller:renderWidget')
            ->convert('widgetPage', 'widget_page_converter:convert');
        $controllers->get('/api/render/{widgetPage}/{widgetId}/draft', 'widget_controller:renderWidget')
            ->convert('widgetPage', 'widget_page_converter:convertToDraft');
        $controllers->get('/api/render/{widgetPage}/{widgetId}/search-results-with-facets', 'widget_controller:renderSearchResultsWidgetWithFacets')
            ->convert('widgetPage', 'widget_page_converter:convert');
        $controllers->get('/api/render/{widgetPage}/{widgetId}/detail', 'widget_controller:renderDetailPage')
            ->convert('widgetPage', 'widget_page_converter:convert');
        $controllers->get('/api/render/{widgetPage}/{widgetId}/tips-embed/{cdbid}', 'widget_controller:renderTipsEmbed')
            ->convert('widgetPage', 'widget_page_converter:convert');

        // Autocompletes
        $controllers->get('/autocomplete/regions/{searchString}', 'widget_controller:getRegionAutocompleteResult');

        $controllers->get('/search', 'widget_controller:searchExample');

        return $controllers;
    }
}
