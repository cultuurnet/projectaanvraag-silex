<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\Widget\Event\SearchResultsQueryAlter;

/**
 * Provides an interface for classes that want to alter the search results.
 */
interface AlterSearchResultsQueryInterface
{

    /**
     * Alter a search results query.
     */
    public function alterSearchResultsQuery(SearchResultsQueryAlter $searchResultsQueryAlter);
}
