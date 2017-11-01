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
    public function getColors($occurrence);

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

    /**
     * Add a font family.
     *
     * @param $fontFamily
     * @return $this
     */
    public function addFontFamily($fontFamily);

    /**
     * Returns an array of font families.
     *
     * @param bool $occurrence
     *  Map and sort font families by occurrence
     * @return array
     */
    public function getFontFamilies($occurrence);

    /**
     * Set the font families.
     *
     * @param array $fontFamilies
     * @return $this
     */
    public function setFontFamilies($fontFamilies);

    /**
     * Add font families to the list of font families.
     *
     * @param array $fontFamilies
     * @return $this
     */
    public function addFontFamilies($fontFamilies);
}
