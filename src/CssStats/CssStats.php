<?php

namespace CultuurNet\ProjectAanvraag\CssStats;

/**
 * Contains CSS statistics including colors, font families, etc
 */
class CssStats implements CssStatsInterface, \JsonSerializable
{
    /**
     * The origin of the scraped css.
     *
     * @var string
     */
    protected $origin;

    /**
     * Array of font families.
     *
     * @var array
     */
    protected $fontFamilies = [];

    /**
     * Array of colors.
     *
     * @var array
     */
    protected $colors = [];

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     * @return CssStats
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
        return $this;
    }

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
        if ($occurrence) {
            $cssColors = array_count_values($this->colors);
            arsort($cssColors);

            return $this->formatValueCount($cssColors);
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

    /**
     * {@inheritdoc}
     */
    public function addFontFamily($fontFamily)
    {
        $this->fontFamilies[] = $fontFamily;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFontFamilies($occurrence = true)
    {
        if ($occurrence) {
            $fontFamilies = array_count_values($this->fontFamilies);
            arsort($fontFamilies);

            return $this->formatValueCount($fontFamilies);
        }

        return $this->fontFamilies;
    }

    /**
     * {@inheritdoc}
     */
    public function setFontFamilies($fontFamilies)
    {
        $this->fontFamilies = $fontFamilies;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFontFamilies($fontFamilies)
    {
        $this->fontFamilies += $fontFamilies;
        return $this;
    }

    /**
     * Format the property count into a keyed array
     * @param array $values
     * @return array
     */
    private function formatValueCount($values)
    {
        $formattedValues = [];

        foreach ($values as $value => $count) {
            $formattedValues[] = [
                'value' => $value,
                'count' => $count,
            ];
        }

        return $formattedValues;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'origin' => $this->getOrigin(),
            'font_families' => $this->getFontFamilies(),
            'colors' => $this->getColors(),
        ];
    }
}
