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
