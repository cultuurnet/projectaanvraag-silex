<?php

namespace CultuurNet\ProjectAanvraag\CssStats;

interface CssStatsServiceInterface
{
    /**
     * Get parsed CSS statistics for a given url.
     *
     * @param $url string
     * @return string
     */
    public function getCssStatsFromUrl($url);

    /**
     * Gets a string of CSS for a given url.
     *
     * @param $url string
     * @return string
     */
    public function getCssFromUrl($url);

    /**
     * Parses a css string.
     *
     * @param string $css
     * @param string $origin
     * @return CssStatsInterface
     */
    public function parseCss($css, $origin);
}
