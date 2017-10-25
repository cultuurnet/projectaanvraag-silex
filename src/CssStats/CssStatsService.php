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
                try {
                    $response = $this->client->get($fileUrl)->send();
                    $cssContent .= (string) $response->getBody();
                } catch (\Throwable $t) {
                    // Catch all exceptions but don't handle them.
                    // We don't care if a css file does not load.
                }
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
        $values = $this->getCssPropertyValues('color', $css);

        // Make sure all color codes are 6 chars
        foreach ($values as $value) {
            // Massage hex values so they are all 6 chars
            if (substr($value, 0, 1) === '#') {
                if (strlen($value) === 4) {
                    $hex = $value;
                    $value = $hex . substr($hex, 1, 3);
                }
            }

            if (!empty($value)) {
                $cssColors[] = $value;
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
        return $this->getCssPropertyValues('font-family', $css);
    }

    /**
     * Get values for a css property from a css string.
     * @param string $property
     * @param $css
     * @return array
     */
    private function getCssPropertyValues($property, $css)
    {
        $properties = [];
        preg_match_all('/[^-]'.$property.'\s*:([\s\S]*?)(;|})/i', $css, $values);

        if (!empty($values[1])) {
            foreach ($values[1] as $value) {
                // Remove some values we don't want
                $blackList = ['!important', 'inherit', 'transparent'];
                $replaced = trim(str_replace($blackList, '', $value));

                if (!empty($replaced)) {
                    $properties[] = $replaced;
                }
            }
        }

        return $properties;
    }
}
