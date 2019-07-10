<?php

namespace CultuurNet\ProjectAanvraag\Widget;

/**
 * Provides a service to search on regions.
 */
class RegionService
{
    /**
     * @var string
     */
    protected $jsonLocation;

    /**
     * RegionService constructor.
     * @param $jsonLocation
     */
    public function __construct($jsonLocation)
    {
        $this->jsonLocation = $jsonLocation;
    }

    /**
     * Provide autocompletion results for the given string.
     * @param $searchString
     */
    public function getAutocompletResults($searchString)
    {
        $data = file_get_contents($this->jsonLocation);

        $searchString = strtolower($searchString);
        $matches = [];
        $regions = json_decode($data);
        if (!empty($regions)) {
            foreach ($regions as $region) {
                if (strpos(strtolower($region->name), $searchString) !== false) {
                    $matches[] = $region->name;
                }
            }
        }

        return $matches;
    }

    /**
     * Get an item by name
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
     * Sort items according to Levenshtein distance
     * @param $matches
     * @param $searchString
     * @return
     */
    public function sortByLevenshtein($matches, $searchString)
    {
        usort($matches, function ($a, $b)
          use ($searchString) {
              $levA = levenshtein($searchString, $a);
              $levB = levenshtein($searchString, $b);

              return $levA === $levB ? 0 : ($levA > $levB ? 1 : -1);
          }
        );
        return $matches;
    }
}
