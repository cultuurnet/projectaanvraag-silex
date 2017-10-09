<?php

namespace CultuurNet\ProjectAanvraag\Widget\Twig;

use CultuurNet\SearchV3\ValueObjects\FacetResult;
use CultuurNet\SearchV3\ValueObjects\FacetResults;
use Guzzle\Http\Url;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * A preproccesor service for widget twig templates.
 */
class TwigPreprocessor
{

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * TwigPreprocessor constructor.
     * @param TranslatorInterface $translator
     * @param \Twig_Environment $twig
     * @param RequestContext $requestContext
     */
    public function __construct(TranslatorInterface $translator, \Twig_Environment $twig, RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param array $events
     *   List of events to preprocess.
     * @param string $langcode
     *   Langcode of the result to show
     * @param array $detail_link_settings
     *   Settings for the links to a detail of every event.
     * @return array
     */
    public function preprocessEventList(array $events, string $langcode, array $settings)
    {

        $preprocessedEvents = [];
        foreach ($events as $event) {
            $preprocessedEvent = $this->preprocessEvent($event, $langcode, $settings['items']);

            $linkType = 'query';
            $detailUrl = $this->request->get('base_url');
            if (isset($settings['general']['detail_link'])) {
                $detailUrl = $settings['general']['detail_link']['url'] ?? $detailUrl;
                $linkType = $settings['general']['detail_link']['cdbid'] ?? $linkType;
            }

            $url = Url::factory($detailUrl);
            if ($linkType === 'url') {
                $url->addPath($preprocessedEvent['id']);
            } else {
                $query = $url->getQuery();
                $query['cdbid'] = $preprocessedEvent['id'];
                $url->setQuery($query);
            }

            $preprocessedEvent['detail_link'] = $url->__toString();

            $preprocessedEvents[] = $preprocessedEvent;
        }

        return $preprocessedEvents;
    }

    /**
     * Preprocess event data for twig templates..
     *
     * @param \CultuurNet\SearchV3\ValueObjects\Event $event
     * @param string $langcode
     * @param array $settings
     *   Settings for the event display
     * @return array
     */
    public function preprocessEvent(\CultuurNet\SearchV3\ValueObjects\Event $event, string $langcode, array $settings)
    {
        $variables = [
            'id' => $event->getCdbid(),
            'name' => $event->getName()[$langcode] ?? null,
            'description' => $event->getDescription()[$langcode] ?? null,
            'image' => $event->getImage(),
            'when_start' => $event->getStartDate() ? $this->formatDate($event->getStartDate(), $langcode) : null,
            'where' => $event->getLocation() ? $event->getLocation()->getName()[$langcode] ?? null : null,
            'organizer' => $event->getOrganizer() ? $event->getOrganizer()->getName() : null,
            'age_range' => ($event->getTypicalAgeRange() ? $this->formatAgeRange($event->getTypicalAgeRange(), $langcode) : null),
            'themes' => $event->getTermsByDomain('theme'),
            'labels' => $event->getLabels(),
            'vlieg' => $this->checkVliegEvent($event->getTypicalAgeRange(), $event->getLabels()),
            'uitpas' => $event->getOrganizer() ? $this->checkUitpasEvent($event->getOrganizer()->getHiddenLabels()) : false,
        ];

        if (!empty($variables['image'])) {
            $url = Url::factory($variables['image']);
            $query = $url->getQuery();
            $query['crop'] = 'auto';
            $query['scale'] = 'both';
            $query['height'] = $settings['image']['height'];
            $query['width'] = $settings['image']['width'];
            $variables['image'] = $url->__toString();
        } elseif ($settings['image']['default_image']) {
            $variables['image'] = $this->request->getScheme() . '://' . $this->request->getHost() . $this->request->getBaseUrl() . '/assets/images/event.png';
        }

        if (!empty($settings['description']['characters'])) {
            $variables['description'] = substr($variables['description'], 0, $settings['description']['characters']);
        }

        $labels = $event->getLabels();
        $languageIconKeywords = [
            'één taalicoon' => 1,
            'twee taaliconen' => 2,
            'drie taaliconen' => 3,
            'vier taaliconen' => 4,
        ];

        // Search for language keywords. Take the highest value of all items that match..
        $totalLanguageIcons = 0;
        if (!empty($labels)) {
            foreach ($languageIconKeywords as $keyword => $value) {
                if (in_array($keyword, $labels)) {
                    $totalLanguageIcons = $value;
                }
            }
        }

        $variables['language_icons']= '';
        if ($totalLanguageIcons) {
            $variables['language_icons'] = $this->twig->render('widgets/language-icons.html.twig', ['score' => $totalLanguageIcons]);
        }

        if (!empty($variables['labels']) && !empty($settings['labels']['limit_labels']) && $settings['labels']['limit_labels']['enabled']) {
            $allowedLabels = explode(', ', $settings['labels']['limit_labels']['labels']);
            $variables['labels'] = array_intersect($variables['labels'], $allowedLabels);
        }

        return $variables;
    }

    /**
     * Preprocess facet for sending to a template.
     *
     * @param FacetResult $facetResult
     * @param $type
     * @param $langcode
     * @return array
     */
    public function preprocessFacet(FacetResult $facetResult, $type, $langcode) {
        $facet = [
            'type' => $type,
            'count' => count($facetResult->getResults()),
            'options' => []
        ];

        switch ($type) {
            case 'type':
                $facet['label'] = 'Type';
                break;
            case 'location':
                $facet['label'] = 'Waar';
                break;
            default:
                $facet['label'] = ucfirst($type);
                break;
        }

        foreach($facetResult->getResults() as $result) {
            $facet['options'][] = [
                'value' => $result->getValue(),
                'count' => $result->getCount(),
                'name' => $result->getNames()[$langcode] ?? '',
            ];
        }

        return $facet;
    }

    /**
     * Preprocess extra facet for sending to a template.
     *
     * @param $filter
     * @return array
     */
    public function preprocessExtraFacet($filter) {
        $facet = [
            'type' => 'extra',
            'label' => $filter['label'] ?? '',
            'count' => count($filter['options']),
            'options' => []
        ];

        foreach($filter['options'] as $option) {
            $facet['options'][] = [
                'value' => $option['query'],
                'name' => $option['label'] ?? '',
            ];
        }

        return $facet;
    }

    /**
     * Return fixed values for date facet.
     *
     * @return array
     */
    public function getDateFacet() {
        return [
            'type' => 'date',
            'label' => 'Wanneer',
            'count' => 6,
            'options' => [
                [
                    'value' => 'today',
                    'name' => 'Vandaag'
                ],
                [
                    'value' => 'tomorrow',
                    'name' => 'Morgen'
                ],
                [
                    'value' => 'thisweekend',
                    'name' => 'Dit weekend'
                ],
                [
                    'value' => 'next7days',
                    'name' => 'Volgende 7 dagen'
                ],
                [
                    'value' => 'next14days',
                    'name' => 'Volgende 14 dagen'
                ],
                [
                    'value' => 'next30days',
                    'name' => 'Volgende 30 dagen'
                ],
            ]
        ];
    }

    /**
     * Format a datetime object to a specific format.
     *
     * @param \DateTime $datetime
     * @param string $langcode
     * @return string
     */
    protected function formatDate(\DateTime $datetime, string $langcode)
    {

        $originalLocale = setlocale(LC_TIME, '0');

        // Switch the time locale to the requested langcode.
        switch ($langcode) {
            case 'nl':
                setlocale(LC_TIME, 'nl_NL');
                break;

            case 'fr':
                setlocale(LC_TIME, 'fr_FR');
        }

        // Format date according to language.
        $fullDate = '';
        switch ($langcode) {
            case 'nl':
                $date = $datetime->format('l d F Y');
                $time = $datetime->format('h:i');
                $fullDate = "$date om $time uur";
                break;
        }

        return $fullDate;
    }

    /**
     * Format an age range value according to langcode.
     *
     * @param string $range
     * @param string $langcode
     * @return string
     */
    protected function formatAgeRange($range, string $langcode)
    {
        // Check for empty range values.
        if ($range == '-') {
            return null;
        }
        // Explode range on dash.
        $explRange = explode('-', $range);

        // Build range string according to language.
        $rangeStr = '';
        switch ($langcode) {
            case 'nl':
                $rangeStr = "Vanaf $explRange[0] jaar tot $explRange[1] jaar.";
                break;
        }

        return $rangeStr;
    }

    /**
     * Check if event is considered a "Vlieg" event and return either
     * the minimum age or a boolean value.
     *
     * @param string $range
     * @param array $labels
     * @return bool|string
     */
    protected function checkVliegEvent($range, $labels)
    {
        // Check age range if there is one.
        if ($range) {
            // Check for empty range values.
            if ($range !== '-') {
                // Explode range on dash.
                $explRange = explode('-', $range);
                // Check min age and return it if it's lower than 12.
                if ($explRange[0] < 12) {
                    return "$explRange[0]+";
                }
            }
        }

        // Check for certain labels that also determine "Vlieg" events.
        return ($labels && count(array_intersect($labels, ['ook voor kinderen', 'uit met vlieg'])) > 0 ? '0+' : false);
    }

    /**
     * Check if event is considered an "Uitpas" event.
     *
     * @param array $hiddenLabels
     * @return bool
     */
    protected function checkUitpasEvent($hiddenLabels)
    {
        // Check for label values containing "Uitpas".
        if ($hiddenLabels) {
            foreach ($hiddenLabels as $label) {
                if (stripos($label, 'uitpas') !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
