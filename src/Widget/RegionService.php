<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Provides a service to search on regions.
 */
class RegionService
{
    /**
     * @var string
     */
    private $jsonLocation;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct($jsonLocation, TranslatorInterface $translator)
    {
        $this->jsonLocation = $jsonLocation;
        $this->translator = $translator;
    }

    /**
     * Provide autocompletion results for the given string.
     *
     * @param $searchString
     */
    public function getAutocompletResults($searchString, $language = 'nl')
    {
        $data = file_get_contents($this->jsonLocation);
        $searchString = strtolower($searchString);
        $matches = [];
        $regions = json_decode($data);
        if (!empty($regions)) {
            foreach ($regions as $region) {
                if ($language === 'nl') {
                    $translatedRegion = $region->name;
                } else {
                    $translatedRegion = $this->translator->trans($region->key, [], 'region', $language);
                    if ($translatedRegion === $region->key) {
                        $translatedRegion = $region->name;
                    }
                }
                if (strpos(strtolower($translatedRegion), $searchString) !== false) {
                    $matches[$region->key] = $translatedRegion;
                }
            }
        }

        return $matches;
    }

    /**
     * Get an item by name
     *
     * @param $searchString
     */
    public function getItemByName($name)
    {
        $data = file_get_contents($this->jsonLocation);

        $regions = json_decode($data);
        if (!empty($regions)) {
            foreach ($regions as $region) {
                if ($region->name === $name) {
                    return $region;
                }
            }
        }
    }

    /**
     * Get an item by a translated name
     *
     * @param $translatedName
     * @param $translatedLanguage
     */
    public function getItemByTranslatedName($translatedName, $translatedLanguage)
    {
        $regions = Yaml::parse(file_get_contents(__DIR__ . '/../../locales/region/'. $translatedLanguage .'.yml'));
        foreach ($regions as $key => $region) {
            if ($region === $translatedName) {
                $matchedRegion = (object) array(
                  'key' => $key,
                  'name' => $region,
                );
                return $matchedRegion;
            }
        }
    }

    /**
     * Sort items according to Levenshtein distance, the higher the match the higher the value.
     * But usort sorts by default from small to large, first item has a smaller value.
     * So the high Levenshtein values need to get before the smaller onces and therefore they need to return -1
     *
     * @param  $matches
     * @param  $searchString
     * @return $matches
     */
    public function sortByLevenshtein($matches, $searchString)
    {
        usort(
            $matches,
            function ($a, $b) use ($searchString) {
                    $levA = levenshtein($searchString, $a);
                    $levB = levenshtein($searchString, $b);
                    return $levA === $levB ? 0 : ($levA > $levB ? -1 : 1);
            }
        );
        return $matches;
    }
}
