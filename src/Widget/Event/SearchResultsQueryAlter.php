<?php

namespace CultuurNet\ProjectAanvraag\Widget\Event;

use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetType\SearchResults;
use CultuurNet\SearchV3\SearchQueryInterface;

/**
 * Event to allow altering of search queries for a search results widget.
 */
class SearchResultsQueryAlter
{

    /**
     * @var string
     */
    private $widgetId;

    /**
     * @var SearchQueryInterface
     */
    private $searchQuery;

    /**
     * SearchResultQueryAlter constructor.
     *
     * @param string $widgetId
     * @param SearchQueryInterface $searchQuery
     */
    public function __construct(string $widgetId, SearchQueryInterface $searchQuery)
    {
        $this->widgetId = $widgetId;
        $this->searchQuery = $searchQuery;
    }

    /**
     * @return string
     */
    public function getWidgetId()
    {
        return $this->widgetId;
    }

    /**
     * @return SearchQueryInterface
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }
}