<?php

namespace CultuurNet\ProjectAanvraag\Widget\Twig;

use CultuurNet\CalendarSummaryV3\CalendarHTMLFormatter;
use CultuurNet\CalendarSummaryV3\CalendarPlainTextFormatter;
use CultuurNet\ProjectAanvraag\Logger;
use CultuurNet\ProjectAanvraag\Utility\TextProcessingTrait;
use CultuurNet\ProjectAanvraag\Widget\Translation\Service\TranslateTerm;
use CultuurNet\ProjectAanvraag\Widget\Translation\Service\FilterForKeyWithFallback;
use CultuurNet\ProjectAanvraag\Curatoren\CuratorenClient;
use CultuurNet\SearchV3\ValueObjects\Event;
use CultuurNet\SearchV3\ValueObjects\FacetResult;
use CultuurNet\SearchV3\ValueObjects\FacetResultItem;
use CultuurNet\SearchV3\ValueObjects\Offer;
use CultuurNet\SearchV3\ValueObjects\Place;
use CultuurNet\SearchV3\ValueObjects\Term;
use CultuurNet\SearchV3\ValueObjects\TranslatedAddress;
use CultuurNet\SearchV3\ValueObjects\TranslatedString;
use Guzzle\Http\Url;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * A preproccesor service for widget twig templates.
 */
class TwigPreprocessor
{

    use TextProcessingTrait;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \CultureFeed
     */
    protected $cultureFeed;

    /**
     * @var string
     */
    protected $socialHost;

    /**
     * @var FilterForKeyWithFallback
     */
    private $filterForKeyWithFallback;

    /**
     * @var TranslateTerm
     */
    private $translateTerm;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CuratorenClient
     */
    protected $curatorenClient;

    /**
     * TwigPreprocessor constructor.
     * @param \Twig_Environment $twig
     * @param RequestContext $requestContext
     */
    public function __construct(
        \Twig_Environment $twig,
        RequestStack $requestStack,
        \CultureFeed $cultureFeed,
        string $socialHost,
        FilterForKeyWithFallback $translateWithFallback,
        TranslateTerm $translateTerm,
        TranslatorInterface $translator,
        CuratorenClient $curatorenClient
    ) {
        $this->twig = $twig;
        $this->request = $requestStack->getCurrentRequest();
        $this->cultureFeed = $cultureFeed;
        $this->socialHost = $socialHost;
        $this->filterForKeyWithFallback = $translateWithFallback;
        $this->translateTerm = $translateTerm;
        $this->translator = $translator;
        $this->curatorenClient = $curatorenClient;
    }

    /**
     * @param array $events
     *   List of events to preprocess.
     * @param $preferredLanguage
     *   Langcode of the result to show
     * @param array $detail_link_settings
     *   Settings for the links to a detail of every event.
     * @return array
     */
    public function preprocessEventList(array $events, $preferredLanguage, array $settings)
    {

        $preprocessedEvents = [];
        foreach ($events as $event) {
            $preprocessedEvent = $this->preprocessEvent($event, $preferredLanguage, $settings['items']);

            $linkType = 'query';
            $detailUrl = $this->request->server->get('HTTP_REFERER');
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

            $referer = parse_url($this->request->server->get('HTTP_REFERER'), PHP_URL_HOST);
            $query = $url->getQuery();
            $query['utm_source'] = urlencode($referer);
            $query['utm_medium'] = 'referral';
            $query['utm_campaign'] = 'widgets';

            $url->setQuery($query);

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
            'name' => $this->translateStringWithFallback($event->getName(), $langcode, $event->getMainLanguage()),
            'description' => $this->translateStringWithFallback($event->getDescription(), $langcode, $event->getMainLanguage()),
            'where' => $event->getLocation() ? $this->preprocessPlace($event->getLocation(), $langcode) : null,
            'when_summary' => $this->formatEventDatesSummary($event, $langcode),
            'expired' => ($event->getEndDate() ? $event->getEndDate()->format('Y-m-d H:i:s') < date('Y-m-d H:i:s') : false),
            'organizer' => $this->translateOrganizerName($event, $langcode),
            'age_range' => ($event->getTypicalAgeRange() ? $this->formatAgeRange($event->getTypicalAgeRange(), $langcode) : null),
            'audience' => ($event->getAudience() ? $event->getAudience()->getAudienceType() : null),
            'themes' => $this->translateTerms($langcode, $event->getTermsByDomain('theme')),
            'labels' => $event->getLabels() ?? [],
            'hidden_labels' => $event->getHiddenLabels() ?? [],
            'vlieg' => $this->isVliegEvent($event),
            'uitpas' => $this->isUitpasEvent($event),
            'museumpas' => $this->isMuseumpasEvent($event),
            'facilities' => $this->getFacilitiesWithPresentInformation($event),
            'typeId' => (count($event->getTermsByDomain('eventtype')) > 0) ? $event->getTermsByDomain('eventtype')[0]->getId() : null,
        ];
        $defaultImage = $settings['image']['default_image'] ? $this->request->getScheme() . '://media.uitdatabank.be/static/uit-placeholder.png' : '';
        $image = $event->getImage() ?? $defaultImage;
        if (!empty($image)) {
            $image = str_replace("http://", "https://", $image);
            $url = Url::factory($image);
            $query = $url->getQuery();
            $query['crop'] = 'auto';
            $query['scale'] = 'both';
            $query['height'] = $settings['image']['height'];
            $query['width'] = $settings['image']['width'];
            $variables['image'] = $url->__toString();
        }

        $variables['image_description'] = 'Default UiT image';
        $variables['copyright'] = null;
        if ($event->getMainMediaObject()) {
            $variables['image_description'] = $event->getMainMediaObject()->getDescription();
            $variables['copyright'] = $event->getMainMediaObject()->getCopyrightHolder();
        }

        $variables['summary'] = strip_tags($variables['description']);
        if (!empty($settings['description']['characters'])) {
            $originalSummary = $variables['summary'];
            $variables['summary'] = $this->createSummary($variables['summary'], $settings['description']['characters']);
            if (strlen($variables['summary']) < strlen($originalSummary)) {
                $variables['summary'] .= substr($variables['summary'], -1) === '.' ? '..' : '..';
            }
        }

        $variables['description'] = str_replace("\n", "<br/>", $variables['description']);
        $variables['description'] = $this->filterXss($variables['description']);

        $languageIconKeywords = [
            'één taalicoon' => 1,
            'twee taaliconen' => 2,
            'drie taaliconen' => 3,
            'vier taaliconen' => 4,
        ];

        // Search for language keywords.
        $totalLanguageIcons = [];
        $mergedLabels = array_merge($variables['labels'], $variables['hidden_labels']);
        if (!empty($mergedLabels)) {
            foreach ($languageIconKeywords as $keyword => $value) {
                if (in_array($keyword, $mergedLabels)) {
                    $totalLanguageIcons[] = $value;
                }
            }
        }

        $variables['language_icons'] = '';
        if ($totalLanguageIcons) {
            $variables['language_icons'] = $this->twig->render('widgets/search-results-widget/language-icons.html.twig', ['languageIcons' => $totalLanguageIcons, 'preferredLanguage' => $langcode]);
        }

        // Strip not allowed types.
        if (!empty($variables['labels']) && !empty($settings['labels']['limit_labels']) && $settings['labels']['limit_labels']['enabled']) {
            $allowedLabels = explode(', ', $settings['labels']['limit_labels']['labels']);
            $variables['labels'] = array_intersect($variables['labels'], $allowedLabels);
        }

        // Add types as first labels, if enabled.
        if (!empty($settings['type']['enabled'])) {
            $variables['type'] = $this->translateLabels($event->getTermsByDomain('eventtype'), $langcode);
        }

        if (!empty($settings['price_information'])) {
            $this->preprocessPriceInfo($event, $variables, $langcode);
        }

        if (!empty($settings['reservation_information'])) {
            $this->preprocessBookingInfo($event, $langcode, $variables);
        }

        if (!empty($settings['editorial_label']) && $settings['editorial_label']['enabled']) {
            $variables['editorial_label'] = $this->preprocessEditorialLabel($event->getCdbid(), $settings['editorial_label']);
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

        $variables['summary'] = '';
        if (!empty($settings['description']['characters'])) {
            $variables['summary'] = $this->createHtmlSummary($variables['description'], $settings['description']['characters']);
            if (strlen($variables['summary']) === strlen($variables['description'])) {
                $variables['summary'] = '';
            }
        }

        $variables['when_details'] = $this->formatEventDatesDetail($event, $langcode);

        // Directions are done via direct link too google.
        if ($event->getLocation()) {
            $directionData = '';
            if ($event->getLocation()->getGeo()) {
                $geoInfo = $event->getLocation()->getGeo();
                $directionData = $geoInfo->getLatitude() . ',' . $geoInfo->getLongitude();
            } else {
                $address = $event->getLocation()->getAddress();
                if ($translatedAddress = $address->getAddressForLanguage($langcode)) {
                    $directionData = $translatedAddress->getStreetAddress() . ' ' . $translatedAddress->getPostalCode() . ' ' . $translatedAddress->getAddressLocality();
                }
            }

            $variables['directions_link'] = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($directionData);
        }

        // Booking information.
        $this->preprocessBookingInfo($event, $langcode, $variables);

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
            $langcodes = array_keys($event->getName()->getValues());
            // Language switch links are based on the languages available for the title.
            foreach ($langcodes as $langcodeItem) {
                $query['langcode'] = $langcodeItem;
                $variables['language_switcher'][$langcodeItem] = '<a href="' . $url->__toString() . '">' . strtoupper($langcodeItem) . '</a>';
            }

            // Share links
            $shareUrl = Url::factory($this->socialHost . '/event/' . $event->getCdbid());
            $shareQuery = $shareUrl->getQuery();
            $shareQuery['origin'] = $_SERVER['HTTP_REFERER'];

            $variables['share_links'] = [
                'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($shareUrl->__toString()),
                'twitter' => 'https://twitter.com/intent/tweet?text='  . urlencode($shareUrl->__toString()),
            ];
        }

        $variables['uitpas_promotions'] = '';
        // Load Uitpas promotions via culturefeed.
        if ($variables['uitpas'] && !empty($settings['uitpas_benefits']) && $event->getOrganizer()) {
            $promotionsQuery = new \CultureFeed_Uitpas_Passholder_Query_SearchPromotionPointsOptions();
            $promotionsQuery->max = 4;
            $promotionsQuery->balieConsumerKey = $event->getOrganizer()->getCdbid();
            $promotionsQuery->unexpired = true;
            $organizerName = $this->translateOrganizerName($event, $langcode);

            try {
                $uitpasPromotions = $this->cultureFeed->uitpas()->getPromotionPoints($promotionsQuery);
                $variables['uitpas_promotions'] = $this->twig->render(
                    'widgets/search-results-widget/uitpas-promotions.html.twig',
                    [
                        'promotions' => $this->preprocessUitpasPromotions($uitpasPromotions),
                        'organizerName' => $organizerName,
                        'organizerUrlName' => $this->formatOrganizerUrlName($organizerName),
                        'organizerId' => $promotionsQuery->balieConsumerKey,
                    ]
                );
            } catch (\Exception $e) {
               // Silent fail.
            }
        }

        // Load 'kansentarief' via culturefeed.
        if (!empty($settings['price_information'])) {
            $this->preprocessPriceInfo($event, $variables, $langcode);
        }

        if (!empty($settings['back_button']['url'])) {
            $variables['back_link'] = $settings['back_button']['url'];
        } else {
            $variables['back_link'] = 'javascript:history.go(-1);';
        }

        return $variables;
    }

    /**
     * Preprocess event articles
     *
     * @param array $articles
     * @param array $settings
     * @return array $articles
     */
    public function preprocessArticles($linkedArticles, $settings)
    {

        $articles = [];
        if (!empty($linkedArticles['hydra:member'])) {
            foreach ($linkedArticles['hydra:member'] as $article) {
                // Reduce text to max allowed characters
                $ellipsis = true;
                $maxChars = 200;
                $referer = parse_url($this->request->server->get('HTTP_REFERER'), PHP_URL_HOST);
                $utmParams = '?utm_source=' . urlencode($referer) . '&utm_medium=referral&utm_campaign=editorial-tools';
                $article['campaign_url'] = $article['url'] . $utmParams;
                $article['text'] =  $this->createSummary($article['text'], $maxChars, $ellipsis);
                $publisher = $article['publisher'];
                $showPublisher = in_array($publisher, $settings['publishers']);
                if (!$settings['limit_publishers'] || ($settings['limit_publishers'] && $showPublisher)) {
                   // only add articles of allowed publishers to array
                    $articles[] = $article;
                }
            }
        }
        return $articles;
    }

    /**
     * Preprocess the editorial label for search results
     * @param string $cdbid
     * @param string $settings
     * @return string|null
     */
    public function preprocessEditorialLabel($cdbid, $settings)
    {
        $articles = $this->curatorenClient->searchArticles($cdbid);

        if (empty($articles['hydra:member'])) {
            return null;
        }

        $publishers = [];
        foreach ($articles['hydra:member'] as $article) {
            $publisher = $article['publisher'];
            $showPublisher = in_array($publisher, $settings['publishers']);
            if (!$settings['limit_publishers'] || ($settings['limit_publishers'] && $showPublisher)) {
                $publishers[] = $publisher;
            }
        }

        if (empty($publishers)) {
            return null;
        }

        $label = 'UiTip van ' . implode(", ", $publishers);
        return $label;
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

    public function formatOrganizerUrlName($organizerName)
    {
        return str_replace(' ', '-', strtolower($organizerName));
    }

    /**
     * Preprocess the price information.
     *
     * @param Event $event
     * @param $variables
     * @param $preferredLanguage
     */
    public function preprocessPriceInfo(Event $event, &$variables, $preferredLanguage)
    {
        $variables['price'] = '';

        $prices = [];
        if ($event->getPriceInfo()) {
            $priceInfos = $event->getPriceInfo();
            foreach ($priceInfos as $priceInfo) {
                /** @var TranslatedString $priceName */
                $priceName = $priceInfo->getName();
                $translatedPriceName = $this->translateStringWithFallback($priceName, $preferredLanguage, $event->getMainLanguage());
                $priceAmount = $priceInfo->getPrice() > 0 ? '&euro; ' . (float) $priceInfo->getPrice() : $this->translator->trans('event_price_free', [], 'messages', $preferredLanguage);
                if ($priceInfo->getCategory() !== 'base') {
                    $prices[] = $translatedPriceName . ': ' . $priceAmount;
                } else {
                    $prices[] = $priceAmount;
                }
            }
        }

        try {
            $query = new \CultureFeed_Uitpas_Event_Query_SearchEventsOptions();
            $query->cdbid = $event->getCdbid();
            $uitpasEvents = $this->cultureFeed->uitpas()->searchEvents($query);
            if (count($uitpasEvents->objects) > 0) {
                $uitpasEvent = $uitpasEvents->objects[0];
                foreach ($uitpasEvent->cardSystems as $cardSystem) {
                    foreach ($cardSystem->distributionKeys as $key) {
                        foreach ($key->conditions as $condition) {
                            if ($condition->definition == $condition::DEFINITION_KANSARM && $key->tariff > 0) {
                                $cardSystemName = $cardSystem->name == 'HELA' ? 'UiTPAS' : $cardSystem->name;
                                if ($condition->value == $condition::VALUE_MY_CARDSYSTEM) {
                                    $prices[] = 'Kansentarief voor ' . $cardSystemName . ': &euro; ' . $key->tariff;
                                }
                                if ($condition->value == $condition::VALUE_AT_LEAST_ONE_CARDSYSTEM) {
                                    $prices[] = 'Kansentarief voor UiTPAS gebruikers uit een andere stad of gemeente: &euro; ' . $key->tariff;
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silent fail.
        }

        foreach ($prices as $key => $price) {
            $prices[$key] = str_replace('.', ',', $price);
        }

        if (count($prices)) {
            $variables['price'] = '<p>' . implode('</p><p>', array_unique($prices)) . '</p>';
        }
    }

    /**
     * Preprocess the booking information.
     *
     * @param Event $event
     * @param $variables
     */
    public function preprocessBookingInfo(Event $event, string $langcode, &$variables)
    {
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
                   'label' => ($this->translateStringWithFallback($bookingInfo->getUrlLabel(), $langcode) !== '') ? $this->translateStringWithFallback($bookingInfo->getUrlLabel(), $langcode, $event->getMainLanguage()) : $bookingInfo->getUrl(),
                ];
            }
        }
    }

    /**
     * Preprocess facet for sending to a template (and check if one is active).
     *
     * @param FacetResult $facetResult
     * @param $type
     * @param $label
     * @param $activeValue
     * @param $preferredLanguage
     * @return array
     */
    public function preprocessFacet(FacetResult $facetResult, $type, $label, $activeValue, $preferredLanguage, bool $isRegionType = false)
    {
        $facet = [
            'type' => $type,
            'label' => $label,
            'count' => count($facetResult->getResults()),
        ];

        $facet += $this->getFacetOptions($facetResult->getResults(), $activeValue, $preferredLanguage, $isRegionType);

        return $facet;
    }

    /**
     * Get the list of facet options based on the given facet items.
     */
    private function getFacetOptions($facetItems, $activeValue, $preferredLanguage, bool $isRegionType = false)
    {
        $hasActive = false;
        $options = [];
        /** @var FacetResultItem $result */
        foreach ($facetItems as $result) {
            /** Ugly hack -> if it's region type than translate it */
            if ($isRegionType) {
                $name = $this->translateRegionFacetResultItemName($preferredLanguage, $result);
            } else {
                $name = $result->getName()->getValueForLanguage($preferredLanguage);
            }

            $option = [
                'value' => $result->getValue(),
                'count' => $result->getCount(),
                'name' => $name,
                'active' => isset($activeValue[$result->getValue()]),
                'children' => [],
            ];

            if ($option['active']) {
                $hasActive = true;
            }

            if ($result->getChildren()) {
                $option['children'] = $this->getFacetOptions($result->getChildren(), $activeValue, $preferredLanguage, $isRegionType);
                if ($option['children']['hasActive']) {
                    $hasActive = true;
                }
            }

            $options[] = $option;
        }

        return [
            'options' => $options,
            'hasActive' => $hasActive,
        ];
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
                'children' => [],
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
        $variables['name'] = $this->translateStringWithFallback($place->getName(), $langcode, $place->getMainLanguage());
        $variables['address'] = [];

        if ($address = $place->getAddress()) {
            /** @var TranslatedAddress $address */
            $translatedAddress = $this->translateAddress($address, $langcode, $place->getMainLanguage());
                $variables['address']['street'] = $translatedAddress->getStreetAddress() ?? '';
                $variables['address']['postalcode'] = $translatedAddress->getPostalCode() ?? '';
                $variables['address']['city'] = $translatedAddress->getAddressLocality() ?? '';
        }

        return $variables;
    }

    /**
     * Return fixed values for date facet (and check if one is active).
     *
     * @param $active
     * @return array
     */
    public function getDateFacet($active, $preferredLanguage = 'nl')
    {
        $facet = [
            'type' => 'when',
            'label' => 'Wanneer',
            'count' => 6,
            'options' => [],
        ];

        $options = [
            'today',
            'tomorrow',
            'thisweekend',
            'next7days',
            'next14days',
            'next30days',
        ];

        foreach ($options as $value) {
            $facet['options'][] = [
                'value' => $value,
                'name' =>  $this->translator->trans($value, [], 'when', $preferredLanguage),
                'active' => ($active == $value ? true : false),
                'children' => [],
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

            case 'en':
                $locale = 'en';
                break;

            case 'nl':
            default:
                $locale = 'nl_NL';
                break;
        }
        $calendarFormatter = new CalendarPlainTextFormatter($locale, false);

        if ($event->getCalendarType() === Offer::CALENDAR_TYPE_MULTIPLE) {
            return $calendarFormatter->format($event, 'sm');
        } else {
            return $calendarFormatter->format($event, 'md');
        }
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

            case 'en':
                $locale = 'en';
                break;

            case 'nl':
            default:
                $locale = 'nl_NL';
                break;
        }

        $hidePastDates = true;
        $calendarFormatter = new CalendarHTMLFormatter($locale, $hidePastDates);
        return $calendarFormatter->format($event, 'lg');
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

        if (empty($explRange[1]) || $explRange[0] === $explRange[1]) {
            return $this->translator->trans('event_age_range_min', ['%min_age%'=>$explRange[0]], 'messages', $langcode);
        }

        if (empty($explRange[0])) {
            return $this->translator->trans('event_age_range_no_min', ['%max_age%'=>$explRange[1]], 'messages', $langcode);
        }

        // Build range string according to language.
        return $this->translator->trans('event_age_range', ['%min_age%'=>$explRange[0], '%max_age%'=>$explRange[1]], 'messages', $langcode);
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
    protected function isUitpasEvent(Event $event)
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

    /**
     * Check if event is considered an "Museumpas" event.
     *
     * @param \CultuurNet\SearchV3\ValueObjects\Event $event
     * @return bool
     */
    protected function isMuseumpasEvent(Event $event)
    {

        $labels = $event->getLabels();
        $labels = array_merge($labels, $event->getHiddenLabels());
        $museumPassLabels = ['museumPASSmusees', 'museumpassmusées', 'Museumpas'];

        if ($labels) {
            foreach ($labels as $label) {
                foreach ($museumPassLabels as $museumPassLabel) {
                    if (stripos($label, $museumPassLabel) !== false) {
                        return true;
                    }
                }
            }

            return false;
        }
    }


    /**
     * Return array of facilities enriched with present information
     *
     * @param \CultuurNet\SearchV3\ValueObjects\Event $event
     * @return array
     */
    protected function getFacilitiesWithPresentInformation(Event $event)
    {
        $presentFacilityIds = [];
        $hasFacilities = false;
        $facilities = $event->getTermsByDomain('facility');

        foreach ($facilities as $facility) {
            $presentFacilityIds[] = $facility->getId();
        }

        $allFacilities = Yaml::parse(file_get_contents(__DIR__ . '/../../../facilities.yml'));
        $enrichedFacilities = [];

        foreach ($allFacilities as $facility) {
            if (in_array($facility['id'], $presentFacilityIds)) {
                $facility['present'] = true;
                $hasFacilities = true;
            } else {
                $facility['present'] = false;
            }

            $enrichedFacilities[] = $facility;
        }

        if ($hasFacilities) {
            return $enrichedFacilities;
        } else {
            return [];
        }
    }

    protected function translateStringWithFallback(?TranslatedString $translatedString, $preferredLanguage, $mainLanguage = 'nl')
    {
        if ($translatedString === null) {
            return '';
        }
        return $this->filterForKeyWithFallback->__invoke($translatedString->getValues(), $preferredLanguage, $mainLanguage);
    }

    protected function translateAddress(TranslatedAddress $translatedAddress, string $prefferedLanguage, $mainLanguage = 'nl')
    {
        $invoke = $this->filterForKeyWithFallback->__invoke($translatedAddress->getAddresses(), $prefferedLanguage, $mainLanguage);
        return $invoke;
    }

    private function translateTerms($preferredLanguage, array $themes): array
    {
        $translatedThemes = [];
        foreach ($themes as $term) {
            $translatedThemes[] = $this->translateTerm->__invoke($term, $preferredLanguage);
        }
        return $translatedThemes;
    }

    private function translateLabels(array $terms, $preferredLanguage): array
    {
        $typeLabels = [];
        /** @var Term $term */
        foreach ($terms as $term) {
            $typeLabels[] = $this->translateTerm($preferredLanguage, $term);
        }

        return $typeLabels;
    }

    private function translateOrganizerName(Event $event, $preferredLanguage): ?string
    {
        if ($event->getOrganizer() === null || $event->getOrganizer()->getName() === null) {
            return null;
        }

        return $this->filterForKeyWithFallback->__invoke(
            $event->getOrganizer()->getName()->getValues(),
            $preferredLanguage
        );
    }

    private function translateTerm($preferredLanguage, Term $term): string
    {
        return $this->translateTerm->__invoke($term, $preferredLanguage);
    }

    /**
     * @param $preferredLanguage
     * @param FacetResultItem $result
     * @return mixed|string
     */
    private function translateRegionFacetResultItemName($preferredLanguage, FacetResultItem $result)
    {
        $translation = $this->translator->trans($result->getValue(), [], 'region', $preferredLanguage);

        if ($translation === $result->getValue()) {
            return $result->getName()->getValueForLanguage($preferredLanguage);
        }

        return $translation;
    }

    /**
     * @param $preferredLanguage
     * @param string $type
     * @param $preferredLanguage
     * @return mixed|string
     */
    public function translateLabel(string $label, string $type, $preferredLanguage = 'nl')
    {
        $translation = $this->translator->trans($label, [], $type, $preferredLanguage);
        return $translation;
    }
}
