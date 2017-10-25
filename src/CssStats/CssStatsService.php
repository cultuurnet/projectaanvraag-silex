<?php

namespace CultuurNet\ProjectAanvraag\CssStats;

use Guzzle\Http\ClientInterface;
use Symfony\Component\DomCrawler\Crawler;

class CssStatsService implements CssStatsServiceInterface
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * CssStatsService constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getCssStatsFromUrl($url)
    {
        // Fetch the css content
        $css = $this->getCssFromUrl($url);

        // Parse
        return $this->parseCss($css);
    }

    /**
     * {@inheritdoc}
     */
    public function getCssFromUrl($url)
    {
        $parsedUrl = parse_url($url);

        // All scraped CSS content
        $cssContent = '';

        $response = $this->client->get($url)->send();
        $document = (string) $response->getBody();

        // Create crawler instance from document
        $crawler = new Crawler($document);

        // Get a list of all CSS files
        $cssFiles = [];
        $crawler->filter('link')->each(
            function ($node) use (&$cssFiles) {
                /** @var $node Crawler */
                if ($node->attr('rel') === 'stylesheet' && !empty($node->attr('href'))) {
                    $cssFiles[] = $node->attr('href');
                }
            }
        );

        // Fetch all CSS content from the stylesheets and build a single string
        foreach ($cssFiles as $fileUrl) {
            // Attempt to parse the file URL
            $url = parse_url($fileUrl);

            // Check for relative paths and prepend the request host
            if (empty($url['scheme']) && substr($fileUrl, 0, 1) === '/') {
                $fileUrl = ($parsedUrl['scheme'] ?? '') . '://' . ($parsedUrl['host'] ?? '') . $url['path'];
            }

            // Check if we have a valid URL before attempting the request
            if (filter_var($fileUrl, FILTER_VALIDATE_URL)) {
                $response = $this->client->get($fileUrl)->send();
                $cssContent .= (string) $response->getBody();
            }
        }

        // Get all inline styles
        $crawler->filter('style')->each(
            function ($node) use (&$cssContent) {
                $cssContent .= $node->text();
            }
        );

        return $cssContent;
    }

    /**
     * {@inheritdoc}
     */
    public function parseCss($css)
    {
        $cssStats = new CssStats();

        // Parse the CSS
        $cssStats->setColors($this->getColors($css));
        $cssStats->setFontFamilies($this->getFontFamilies($css));

        die(print_r($cssStats->getFontFamilies()));

        return $cssStats;
    }

    /**
     * Get's an array of colors from a string of css.
     *
     * @param $css
     * @return array
     */
    private function getColors($css)
    {
        $cssColors = [];
        preg_match_all('/#([a-f0-9]{3}){1,2}\b/i', $css, $matches);

        // Make sure all color codes are 6 chars
        if (!empty($matches[0])) {
            foreach ($matches[0] as $key => $match) {
                $color = ltrim($match, '#');
                $cssColors[] = strlen($color) === 3 ? '#' . strtoupper($color . $color) : '#' .  strtoupper($color);
            }
        }

        return $cssColors;
    }

    /**
     * Get's an array of font families from a string of css.
     *
     * @param $css
     * @return array
     */
    private function getFontFamilies($css)
    {
        $fontFamilies = [];
        preg_match_all('/font-family:([\s\S]*?)(;|})/i', $css, $matches);

        // Make sure all color codes are 6 chars
        if (!empty($matches[1])) {
            foreach ($matches[1] as $key => $match) {
                $fontFamilies[] = $match;
            }
        }

        return $fontFamilies;
    }
}
