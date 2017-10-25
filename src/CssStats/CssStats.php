<?php

namespace CultuurNet\ProjectAanvraag\CssStats;

/**
 * Contains CSS statistics including colors, font families, etc
 */
class CssStats implements CssStatsInterface
{
    /**
     * Array of font families.
     *
     * @var array
     */
    protected $fonts = [];

    /**
     * Array of colors.
     *
     * @var array
     */
    protected $colors = [];

    /**
     * {@inheritdoc}
     */
    public function addColor($color)
    {
        $this->colors[] = $color;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getColors($occurrence = true)
    {
        // Count and sort
        if ($occurrence) {
            $cssColors = array_count_values($this->colors);
            arsort($cssColors);

            return $cssColors;
        }

        return $this->colors;
    }

    /**
     * {@inheritdoc}
     */
    public function setColors($colors)
    {
        $this->colors = $colors;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addColors($colors)
    {
        $this->colors += $colors;
        return $this;
    }
}
