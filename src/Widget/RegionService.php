<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Provides a service to search on regions.
 */
class RegionService
{
    private const SCORE_EXACT = 6; // Exact match
    private const SCORE_EXACT_SUFFIX_PREFIX = 5; // Matches like `Regio X`, `Provincie X`, or `X + deelgemeenten` for search string `X`
    private const SCORE_TYPEAHEAD_COMPLETE = 4; // Typeahead match with a whitespace afterward, e.g. `Zele (Zele)` for search string `Zele`
    private const SCORE_TYPEAHEAD_PARTIAL = 3; // Typeahead match with a non-whitespace character afterward, e.g. `Zelem (Halen)` for search string `Zele`
    private const SCORE_SUBSTRING = 2; // If the search string is not at the start but after a ( or -, e.g. `Hypothetical example (Zele)` for search string `Zele`
    private const SCORE_PARTIAL = 1; // Other partial match, e.g. `Balegem (Oosterzele)` or `Elzele + deelgemeente` for search string `Zele`

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
                $compareString = strtolower($translatedRegion);

                // We compare the strings in multiple ways and give a score based on the kind of match, to avoid that
                // when you search for cities or towns with short names you first get a lot of irrelevant matches that
                // also contain that name or even that they are completely omitted because of a visual limit in the FE.
                // (E.g. Zele or Egem)
                // @see https://jira.publiq.be/browse/WID-575
                // @see https://jira.publiq.be/browse/WID-588

                // Exact matches get the highest relevancy score
                if ($compareString === $searchString) {
                    $matches[$region->key] = [
                        'score' => self::SCORE_EXACT,
                        'name'  => $translatedRegion,
                    ];
                    continue;
                }

                // Exact matches with a known suffix / prefix like `Regio X`, `X + deelgemeenten`, ... get the 2nd
                // highest score. (Keep in mind that everything is already converted to lowercase)
                // Otherwise "Provincie Antwerpen" would get buried by all submunicipalities of Antwerpen
                if ($compareString === 'regio ' . $searchString ||
                    $compareString === $searchString . ' + deelgemeenten' ||
                    $compareString === 'provincie ' . $searchString) {
                    $matches[$region->key] = [
                        'score' => self::SCORE_EXACT_SUFFIX_PREFIX,
                        'name'  => $translatedRegion,
                    ];
                    continue;
                }

                // Typeahead matches get the 3rd / 4th highest relevancy score based on the character
                // after the match
                $position = strpos($compareString, $searchString);
                if ($position === 0) {
                    // If the character after the match is a space, give it a higher score than if it's a non-whitespace
                    // character.
                    // e.g. `Zele (Zele)` is more likely to be relevant than `Zelem (Halen)` for a search on `Zele`
                    $nextCharacter = $compareString[$position + strlen($searchString)];
                    $matches[$region->key] = [
                        'score' => $nextCharacter === ' ' ? self::SCORE_TYPEAHEAD_COMPLETE : self::SCORE_TYPEAHEAD_PARTIAL,
                        'name'  => $translatedRegion,
                    ];
                    continue;
                }

                // We also promote matches that are not at the start of the string but after a `(` or `-`
                // to have better support for submunicipalities (like "Kessel-Lo (Leuven)" for the query "Leuven")
                // and municipality names with prefixes like Saint (like "Sint-Amands" for the query "Amands")
                if ($position !== false && $position > 0 && in_array($compareString[$position-1], ['(', '-'])) {
                    $matches[$region->key] = [
                        'score' => self::SCORE_SUBSTRING,
                        'name'  => $translatedRegion,
                    ];
                    continue;
                }

                // Generic partial matches get the lowest relevancy score (but are also included)
                if ($position !== false) {
                    $matches[$region->key] = [
                        'score' => self::SCORE_PARTIAL,
                        'name'  => $translatedRegion,
                    ];
                }
            }
        }

        // Sort the matches by score (but keep the keys)
        uasort(
            $matches,
            function ($a, $b) {
                // Reverse the result using *-1 so higher scores are sorted before lower scores (instead of after)
                return ($a['score'] <=> $b['score']) * -1;
            }
        );

        // Convert the matches to string values before returning
        return array_map(
            function ($match) {
                return $match['name'];
            },
            $matches
        );
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
}
