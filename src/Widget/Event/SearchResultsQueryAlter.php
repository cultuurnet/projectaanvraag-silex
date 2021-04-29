<?php

namespace CultuurNet\ProjectAanvraag\Widget\Event;

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
     * @var array
     */
    private $activeFilters;

    /**
     * SearchResultQueryAlter constructor.
     *
     * @param string $widgetId
     * @param SearchQueryInterface $searchQuery
     */
    public function __construct(string $widgetId, SearchQueryInterface $searchQuery, array $activeFilters)
    {
        $this->widgetId = $widgetId;
        $this->searchQuery = $searchQuery;
        $this->activeFilters = $activeFilters;
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

    /**
     * @return array
     */
    public function getActiveFilters()
    {
        return $this->activeFilters;
    }

    /**
     * Set the active filters.
     * @param array $activeFilters
     */
    public function setActiveFilters(array $activeFilters)
    {
        $this->activeFilters = $activeFilters;
        return $this;
    }
}
