<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\SearchV3\SearchQueryInterface;

/**
 * Provides an interface for classes that want to alter the search results.
 */
interface AlterSearchResultsQueryInterface
{

    /**
     * Alter the given search results query for a given widget id..
     */
    public function alterSearchResultsQuery(string $searchResultswidgetId, SearchQueryInterface $searchQuery);
}
