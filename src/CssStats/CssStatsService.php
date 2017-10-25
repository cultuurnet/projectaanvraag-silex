<?php

namespace CultuurNet\ProjectAanvraag\CssStats;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class CssStatsService implements CssStatsServiceInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * CssStatsService constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
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
        // All scraped CSS content
        $cssContent = '';

        $resonse = $this->client->get($url);
        $document = $resonse->getBody()->getContents();

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
            $response = $this->client->get($fileUrl);
            $cssContent .= $response->getBody()->getContents();
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

        // Colors
        $colors = $this->getColors($css);
        $cssStats->setColors($colors);

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
        preg_match_all('/#([a-f0-9]{3}){1,2}\b/i', $css, $matches);

        $cssColors = [];

        // Make sure all color codes are 6 chars
        if (!empty($matches[0])) {
            foreach ($matches[0] as $key => $match) {
                $color = ltrim($match, '#');
                $cssColors[] = strlen($color) === 3 ? '#' . strtoupper($color . $color) : '#' .  strtoupper($color);
            }
        }

        return $cssColors;
    }
}
