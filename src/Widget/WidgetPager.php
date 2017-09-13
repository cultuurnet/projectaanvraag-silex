<?php

namespace CultuurNet\ProjectAanvraag\Widget;

/**
 * Provides a class for widget pagers.
 */
class WidgetPager
{

    /**
     * Total pages.
     * @var int
     */
    public $pageCount;

    /**
     * Current page
     * @var int
     */
    public $activePageIndex;

    /**
     * Items to show per page
     * @var int
     */
    public $itemsPerPage;

    /**
     * Total items visible before current page.
     * @var int
     */
    public $visibleBefore = 2;

    /**
     * Total items visible after current page.
     * @var int
     */
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

    /**
     * Calculate lowest visible page number.
     *
     * @return int
     */
    public function getLowestVisible()
    {
        if (($this->activePageIndex - 1) <= $this->visibleBefore) {
            return 1;
        } else {
            // Return lowest visible + correction.
            return $this->activePageIndex - $this->visibleBefore + 1;
        }
    }

    /**
     * Calculate highest visible page number.
     *
     * @return int
     */
    public function getHighestVisible()
    {
        if (($this->activePageIndex + $this->visibleAfter) >= $this->pageCount) {
            return $this->pageCount;
        } else {
            // Return highest visible + correction.
            return $this->activePageIndex + $this->visibleAfter + 1;
        }
    }
}
