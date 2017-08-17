<?php

namespace CultuurNet\ProjectAanvraag\Widget;

/**
 * Provides a class for widget pagers.
 */
class WidgetPager
{
    public $pageCount;

    public $activePageIndex;

    public $itemsPerPage;

    public $visibleBefore = 2;

    public $visibleAfter = 2;

    /**
     * WidgetPager constructor.
     *
     * @param int $pageCount
     * @param int $activePageIndex
     * @param int $itemsPerPage
     */
    public function __construct(int $pageCount, int $activePageIndex, int $itemsPerPage)
    {
        $this->pageCount = $pageCount;
        $this->activePageIndex = $activePageIndex;
        $this->itemsPerPage = $itemsPerPage;
    }

    public function getLowestVisible()
    {
        // TODO
    }

    public function getHighestVisible()
    {
        // TODO
    }

}
