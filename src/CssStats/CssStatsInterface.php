<?php

namespace CultuurNet\ProjectAanvraag\CssStats;

/**
 * Defines a CSS stats interface.
 */
interface CssStatsInterface
{
    /**
     * Add a color.
     *
     * @param $color
     * @return $this
     */
    public function addColor($color);

    /**
     * Returns an array of colors.
     *
     * @param bool $occurrence
     *  Map and sort colors by occurrence
     * @return array
     */
    public function getColors($occurrence = true);

    /**
     * Set the colors.
     *
     * @param array $colors
     * @return $this
     */
    public function setColors($colors);

    /**
     * Add colors to the list of colors.
     *
     * @param array $colors
     * @return $this
     */
    public function addColors($colors);
}
