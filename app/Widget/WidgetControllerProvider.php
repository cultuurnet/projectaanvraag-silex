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
            return new WidgetController($app['widget_renderer'], $app['widget_repository'], $app['mongodb'], $app['search_api'], $app['widget_page_deserializer'], $app['debug'], $app['widget_region_service']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/layout/{widgetPage}.js', 'widget_controller:renderPage')
            ->convert('widgetPage', 'widget_page_convertor:convert');

        // Render widgets.
        $controllers->get('/api/render/{widgetPage}/{widgetId}', 'widget_controller:renderWidget')
            ->convert('widgetPage', 'widget_page_convertor:convert');
        $controllers->get('/api/render/{widgetPage}/{widgetId}/draft', 'widget_controller:renderWidget')
            ->convert('widgetPage', 'widget_page_convertor:convertToDraft');
        $controllers->get('/api/render/{widgetPage}/{widgetId}/search-results-with-facets', 'widget_controller:renderSearchResultsWidgetWithFacets')
            ->convert('widgetPage', 'widget_page_convertor:convert');
        $controllers->get('/api/render/{widgetPage}/{widgetId}/detail', 'widget_controller:renderDetailPage')
            ->convert('widgetPage', 'widget_page_convertor:convert');

        // Autocompletes
        $controllers->get('/autocomplete/regions/{searchString}', 'widget_controller:getRegionAutocompleteResult');

        $controllers->get('/search', 'widget_controller:searchExample');

        return $controllers;
    }
}
