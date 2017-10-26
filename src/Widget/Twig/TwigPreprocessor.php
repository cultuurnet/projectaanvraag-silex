<?php

namespace CultuurNet\ProjectAanvraag\Widget\Twig;

use CultuurNet\SearchV3\ValueObjects\Event;
use CultuurNet\SearchV3\ValueObjects\FacetResult;
use CultuurNet\SearchV3\ValueObjects\FacetResults;
use CultuurNet\SearchV3\ValueObjects\Offer;
use CultuurNet\SearchV3\ValueObjects\Place;
use Guzzle\Http\Url;
use IntlDateFormatter;
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

    protected $cultureFeed;

    /**
     * TwigPreprocessor constructor.
     * @param TranslatorInterface $translator
     * @param \Twig_Environment $twig
     * @param RequestContext $requestContext
     */
    public function __construct(TranslatorInterface $translator, \Twig_Environment $twig, RequestStack $requestStack, \CultureFeed $cultureFeed)
    {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->request = $requestStack->getCurrentRequest();
        $this->cultureFeed = $cultureFeed;
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
    public function preprocessEvent(Event $event, string $langcode, array $settings)
    {
        $variables = [
            'id' => $event->getCdbid(),
            'name' => $event->getName()[$langcode] ?? null,
            'description' => $event->getDescription()[$langcode] ?? null,
            'when_summary' => $this->formatEventDatesSummary($event, $langcode),
            'where' => $event->getLocation() ? $event->getLocation()->getName()[$langcode] ?? null : null,
            'organizer' => $event->getOrganizer() ? $event->getOrganizer()->getName() : null,
            'age_range' => ($event->getTypicalAgeRange() ? $this->formatAgeRange($event->getTypicalAgeRange(), $langcode) : null),
            'themes' => $event->getTermsByDomain('theme'),
            'labels' => $event->getLabels() ?? [],
            'vlieg' => $this->isVliegEvent($event),
            'uitpas' => $this->isUitpasEvent($event),
        ];

        $defaultImage = $settings['image']['default_image'] ? $this->request->getScheme() . '://media.uitdatabank.be/static/uit-placeholder.png' : '';
        $image = $event->getImage() ?? $defaultImage;
        if (!empty($image)) {
            $url = Url::factory($image);
            $query = $url->getQuery();
            $query['crop'] = 'auto';
            $query['scale'] = 'both';
            $query['height'] = $settings['image']['height'];
            $query['width'] = $settings['image']['width'];
            $variables['image'] = $url->__toString();
        }

        if (!empty($settings['description']['characters'])) {
            $variables['description'] = substr($variables['description'], 0, $settings['description']['characters']);
        }

        $languageIconKeywords = [
            'één taalicoon' => 1,
            'twee taaliconen' => 2,
            'drie taaliconen' => 3,
            'vier taaliconen' => 4,
        ];

        // Search for language keywords. Take the highest value of all items that match..
        $totalLanguageIcons = 0;
        if (!empty($variables['labels'])) {
            foreach ($languageIconKeywords as $keyword => $value) {
                if (in_array($keyword, $variables['labels'])) {
                    $totalLanguageIcons = $value;
                }
            }
        }

        $variables['language_icons'] = '';
        if ($totalLanguageIcons) {
            $variables['language_icons'] = $this->twig->render('widgets/search-results-widget/language-icons.html.twig', ['score' => $totalLanguageIcons]);
        }

        // Strip not allowed types.
        if (!empty($variables['labels']) && !empty($settings['labels']['limit_labels']) && $settings['labels']['limit_labels']['enabled']) {
            $allowedLabels = explode(', ', $settings['labels']['limit_labels']['labels']);
            $variables['labels'] = array_intersect($variables['labels'], $allowedLabels);
        }

        // Add types as first labels, if enabled.
        if (!empty($settings['type']['enabled'])) {
            $types = $event->getTermsByDomain('eventtype');
            $typeLabels = [];
            if (!empty($types)) {
                foreach ($types as $type) {
                    $typeLabels[] = $type->getLabel();
                }
            }

            $variables['type'] = $typeLabels;
        }

        return $variables;
    }

    /**
     * Preprocess an event detail page.
     *
     * @param Event $event
     * @param string $langcode
     * @param array $settings
     */
    public function preprocessEventDetail(Event $event, string $langcode, array $settings)
    {
        $variables = $this->preprocessEvent($event, $langcode, $settings);

        $variables['where'] = $event->getLocation() ? $this->preprocessPlace($event->getLocation(), $langcode) : null;
        $variables['when_details'] = $this->formatEventDatesDetail($event, $langcode);

        // Directions are done via direct link too google.
        if ($event->getLocation()) {
            $directionData = '';
            if ($event->getLocation()->getGeo()) {
                $geoInfo = $event->getLocation()->getGeo();
                $directionData = $geoInfo->getLatitude() . ',' . $geoInfo->getLongitude();
            } else {
                $address = $event->getLocation()->getAddress();
                $directionData = $address->getStreetAddress() . ' ' . $address->getPostalCode() . ' ' . $address->getAddressLocality();
            }

            $variables['directions_link'] = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($directionData);
        }

        // Price information.
        $variables['price'] = '';
        if ($event->getPriceInfo()) {
            $priceInfo = $event->getPriceInfo()[0];
            $variables['price'] = $priceInfo->getPrice() > 0 ? '&euro; ' . (float) $priceInfo->getPrice() : 'gratis';
            $variables['price'] = str_replace('.', ',', $variables['price']);
        }

        // Booking information.
        $variables['booking_info'] = [];
        if ($event->getBookingInfo()) {
            $bookingInfo = $event->getBookingInfo();
            $variables['booking_info'] = [];
            if ($bookingInfo->getEmail()) {
                $variables['booking_info']['email'] = $bookingInfo->getEmail();
            }
            if ($bookingInfo->getPhone()) {
                $variables['booking_info']['phone'] = $bookingInfo->getPhone();
            }
            if ($bookingInfo->getUrl()) {
                $variables['booking_info']['url'] = [
                    'url' => $bookingInfo->getUrl(),
                    'label' => $bookingInfo->getUrlLabel() ?? $bookingInfo->getUrl(),
                ];
            }
        }

        // Contact info.
        $variables['contact_info'] = [];
        $variables['links'] = [];
        if ($event->getContactPoint()) {
            $contactPoint = $event->getContactPoint();
            $variables['contact_info']['emails'] = $contactPoint->getEmails();
            $variables['contact_info']['phone_numbers'] = $contactPoint->getPhoneNumbers();
            $variables['links'] = $contactPoint->getUrls();
        }

        // Language links.
        $variables['language_switcher'] = [];
        $variables['share_links'] = [];
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $url = Url::factory($_SERVER['HTTP_REFERER']);

            $query = $url->getQuery();
            // Language switch links are based on the languages available for the title.
            foreach (array_keys($event->getName()) as $langcode) {
                $query['langcode'] = $langcode;
                $variables['language_switcher'][$langcode] = '<a href="' . $url->__toString() . '">' . strtoupper($langcode) . '</a>';
            }

            // Share links
            $shareUrl = Url::factory($this->request->getSchemeAndHttpHost() . '/event/' . $event->getCdbid());
            $shareQuery = $shareUrl->getQuery();
            $shareQuery['origin'] = $_SERVER['HTTP_REFERER'];

            $variables['share_links'] = [
                'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($shareUrl->__toString()),
                'twitter' => 'https://twitter.com/intent/tweet?text='  . urlencode($shareUrl->__toString()),
                'google_plus' => 'https://plus.google.com/share?url=' . urlencode($shareUrl->__toString()),
            ];
        }

        $variables['uitpas_promotions'] = '';
        if ($variables['uitpas'] && $event->getOrganizer()) {
            $promotionsQuery = new \CultureFeed_Uitpas_Passholder_Query_SearchPromotionPointsOptions();
            $promotionsQuery->balieConsumerKey = $event->getOrganizer()->getCdbid();

            try {
                $uitpasPromotions = $this->cultureFeed->uitpas()->getPromotionPoints($promotionsQuery);
                $variables['uitpas_promotions'] = $this->twig->render('widgets/search-results-widget/uitpas-promotions.html.twig', ['promotions' => $this->preprocessUitpasPromotions($uitpasPromotions)]);
            } catch (\Exception $e) {
               // Silent fail.
            }
        }

        return $variables;
    }

    /**
     * Preprocess the uitpas promotions.
     * @param \CultureFeed_ResultSet $resultSet
     */
    public function preprocessUitpasPromotions(\CultureFeed_ResultSet $resultSet)
    {

        $promotions = [];
        /** @var \CultureFeed_Uitpas_Passholder_PointsPromotion $object */
        foreach ($resultSet->objects as $object) {
            $promotions[] = [
                'title' => $object->title,
                'points' => $object->points,
            ];
        }

        return $promotions;
    }

    /**
     * Preprocess facet for sending to a template (and check if one is active).
     *
     * @param FacetResult $facetResult
     * @param $type
     * @param $label
     * @param $activeValue
     * @return array
     */
    public function preprocessFacet(FacetResult $facetResult, $type, $label, $activeValue)
    {
        $facet = [
            'type' => $type,
            'label' => $label,
            'count' => count($facetResult->getResults()),
            'options' => [],
        ];

        foreach ($facetResult->getResults() as $result) {
            $facet['options'][] = [
                'value' => $result->getValue(),
                'count' => $result->getCount(),
                'name' => $result->getNames()['nl'] ?? '',
                'active' => ($activeValue == $result->getValue() ? true : false),
            ];
        }

        return $facet;
    }

    /**
     * Preprocess a custom facet (group filter) for sending to a template (and check which options are active)
     *
     * @param $filter
     * @param $index
     * @param $actives
     * @return array
     */
    public function preprocessCustomFacet($filter, $index, $actives)
    {
        $facet = [
            'type' => 'custom',
            'label' => $filter['label'] ?? '',
            'id' => $index,
            'count' => count($filter['options']),
            'options' => [],
        ];

        foreach ($filter['options'] as $i => $option) {
            $facet['options'][] = [
                'value' => $option['query'],
                'name' => $option['label'] ?? '',
                'active' => (isset($actives[$i]) ? true : false),
            ];
        }

        return $facet;
    }

    /**
     * Preprocess a place.
     * @param Place $place
     * @param $langcode
     */
    public function preprocessPlace(Place $place, $langcode)
    {

        $variables = [];
        $variables['name'] = $place->getName()[$langcode] ?? null;
        $variables['address'] = [];
        if ($address = $place->getAddress()) {
            $variables['address']['street'] = $address->getStreetAddress();
            $variables['address']['postalcode'] = $address->getPostalCode();
            $variables['address']['city'] = $address->getAddressLocality();
        }

        return $variables;
    }

    /**
     * Return fixed values for date facet (and check if one is active).
     *
     * @param $active
     * @return array
     */
    public function getDateFacet($active)
    {
        $facet = [
            'type' => 'when',
            'label' => 'Wanneer',
            'count' => 6,
            'options' => [],
        ];

        $options = [
            'today' => 'Vandaag',
            'tomorrow' => 'Morgen',
            'thisweekend' => 'Dit weekend',
            'next7days' => 'Volgende 7 dagen',
            'next14days' => 'Volgende 14 dagen',
            'next30days' => 'Volgende 30 dagen',
        ];

        foreach ($options as $value => $label) {
            $facet['options'][] = [
                'value' => $value,
                'name' => $label,
                'active' => ($active == $value ? true : false),
            ];
        }

        return $facet;
    }

    /**
     * Format all the event dates to 1 summary variable.
     * @param Event $event
     */
    protected function formatEventDatesSummary(Event $event, string $langcode)
    {

        // Switch the time locale to the requested langcode.
        switch ($langcode) {
            case 'fr':
                $locale = 'fr_FR';
                break;

            case 'nl':
            default:
                $locale = 'nl_NL';
                break;
        }

        $summary = '';
        // Multiple and periodic events should show from and to date.
        if ($event->getCalendarType() === Offer::CALENDAR_TYPE_MULTIPLE || $event->getCalendarType() === Offer::CALENDAR_TYPE_PERIODIC) {
            $dateFormatter = new IntlDateFormatter(
                $locale,
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL,
                date_default_timezone_get(),
                IntlDateFormatter::GREGORIAN,
                'd MMMM Y'
            );

            $dateParts = [];

            if ($event->getStartDate()) {
                $dateParts[] = 'van ' . $dateFormatter->format($event->getStartDate());
            }

            if ($event->getEndDate()) {
                $dateParts[] = 'tot ' . $dateFormatter->format($event->getEndDate());
            }

            $summary = implode($dateParts, ' ');
        } elseif ($event->getCalendarType() === Offer::CALENDAR_TYPE_SINGLE) {
            $dateFormatter = new IntlDateFormatter(
                $locale,
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL,
                date_default_timezone_get(),
                IntlDateFormatter::GREGORIAN,
                'd MMMM Y'
            );

            $summary = $dateFormatter->format($event->getStartDate());
        }

        return $summary;
    }

    /**
     * Format the event dates for the detail page.
     *
     * @param Event $event
     * @param string $langcode
     */
    protected function formatEventDatesDetail(Event $event, string $langcode)
    {

        // Switch the time locale to the requested langcode.
        switch ($langcode) {
            case 'fr':
                $locale = 'fr_FR';
                break;

            case 'nl':
            default:
                $locale = 'nl_NL';
                break;
        }

        if ($event->getCalendarType() === Offer::CALENDAR_TYPE_SINGLE) {
            return $this->formatSingleDate($event->getStartDate(), $event->getEndDate(), $locale);
        } elseif ($event->getCalendarType() === Offer::CALENDAR_TYPE_PERIODIC) {
            return $this->formatPeriod($event->getStartDate(), $event->getEndDate(), $locale);
        } elseif ($event->getCalendarType() === Offer::CALENDAR_TYPE_MULTIPLE) {
            $output = '<ul>';
            $subEvents = $event->getSubEvents();
            $now = new \DateTime();
            foreach ($subEvents as $subEvent) {
                if ($subEvent->getEndDate() > $now) {
                    $output .= '<li>' . $this->formatSingleDate($subEvent->getStartDate(), $subEvent->getEndDate(), $locale) . '</li>';
                }
            }
            $output .= '</ul>';

            return $output;
        }
    }

    /**
     * Format the given start and end date as period.
     *
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param $locale
     * @return string
     */
    protected function formatPeriod(\DateTime $dateFrom, \DateTime $dateTo, $locale)
    {

        $dateFormatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            date_default_timezone_get(),
            IntlDateFormatter::GREGORIAN,
            'd MMMM yyyy'
        );

        $intlDateFrom = $dateFormatter->format($dateFrom);
        $intlDateTo = $dateFormatter->format($dateTo);

        $output = '<p class="cf-period">';
        $output .= '<span class="cf-from cf-meta">van</span>';
        $output .= '<time itemprop="startDate" datetime="' . $dateFrom->format('Y-m-d') . '">';
        $output .= '<span class="cf-date">' . $intlDateFrom . '</span> </time>';
        $output .= '<span class="cf-to cf-meta">tot</span>';
        $output .= '<time itemprop="endDate" datetime="' . $dateTo->format('Y-m-d') . '">';
        $output .= '<span class="cf-date">' . $intlDateTo . '</span> </time>';
        $output .= '</p>';

        return $output;
    }

    /**
     * Format a single date.
     *
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param $locale
     */
    protected function formatSingleDate(\DateTime $dateFrom, \DateTime $dateTo, $locale)
    {

        $weekDayFormatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            date_default_timezone_get(),
            IntlDateFormatter::GREGORIAN,
            'EEEE'
        );

        $dateFormatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            date_default_timezone_get(),
            IntlDateFormatter::GREGORIAN,
            'd MMMM yyyy'
        );

        $timeFormatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            new \DateTimeZone('Europe/Brussels'),
            IntlDateFormatter::GREGORIAN,
            'HH:mm'
        );


        $startTime = $timeFormatter->format($dateFrom);
        $endTime = $timeFormatter->format($dateTo);

        if (!empty($startTime)) {
            $output = '<time itemprop="startDate" datetime="' . $dateFrom->format('Y-m-d') . 'T' . $startTime . '">';
        } else {
            $output = '<time itemprop="startDate" datetime="' . $dateFrom->format('Y-m-d') . '">';
        }

        $output .= '<span class="cf-weekday cf-meta">' . $weekDayFormatter->format($dateFrom) . '</span>';
        $output .= ' ';
        $output .= '<span class="cf-date">' . $dateFormatter->format($dateFrom) . '</span>';

        if (!empty($startTime)) {
            $output .= ' ';
            if (!empty($endTime)) {
                $output .= '<span class="cf-from cf-meta">van</span>';
                $output .= ' ';
            } else {
                $output .= '<span class="cf-from cf-meta">om</span>';
                $output .= ' ';
            }
            $output .= '<span class="cf-time">' . $startTime . '</span>';
            $output .= '</time>';
            if (!empty($endTime)) {
                $output .= ' ';
                $output .= '<span class="cf-to cf-meta">tot</span>';
                $output .= ' ';
                $output .= '<time itemprop="endDate" datetime="' . $dateTo->format('Y-m-d') . 'T' . $endTime . '">';
                $output .= '<span class="cf-time">' . $endTime . '</span>';
                $output .= '</time>';
            }
        } else {
            $output .= ' </time>';
        }
        return $output;
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

        if ($explRange[0] === $explRange[1]) {
            return $explRange[0] . ' jaar';
        }
        
        // Build range string according to language.
        return "Vanaf $explRange[0] jaar tot $explRange[1] jaar.";
    }

    /**
     * Check if event is considered a "Vlieg" event and return either
     * the minimum age or a boolean value.
     *
     * @param string $range
     * @param array $labels
     * @return bool|string
     */
    protected function isVliegEvent(Event $event)
    {
        $range = $event->getTypicalAgeRange();
        $labels = $event->getLabels();
        $labels = array_merge($labels, $event->getHiddenLabels());

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
     * @param \CultuurNet\SearchV3\ValueObjects\Event $event
     * @return bool
     */
    protected function isUitpasEvent(\CultuurNet\SearchV3\ValueObjects\Event $event)
    {

        $labels = $event->getLabels();
        $labels = array_merge($labels, $event->getHiddenLabels());

        // Check for label values containing "Uitpas".
        if ($labels) {
            foreach ($labels as $label) {
                if (stripos($label, 'uitpas') !== false || stripos($label, 'paspartoe') !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
