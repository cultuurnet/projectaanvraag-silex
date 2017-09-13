<?php

namespace CultuurNet\ProjectAanvraag\Widget\Twig;

use Guzzle\Http\Url;
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
     * TwigPreprocessor constructor.
     * @param TranslatorInterface $translator
     * @param \Twig_Environment $twig
     */
    public function __construct(TranslatorInterface $translator, \Twig_Environment $twig)
    {
        $this->translator = $translator;
        $this->twig = $twig;
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
    public function preprocessEventList(array $events, string $langcode, array $settings) {

        $preprocessed_events = [];
        foreach ($events as $event) {
            $preprocessed_event = $this->preprocessEvent($event, $langcode, $settings['items']);

            if (isset($settings['general']['detail_link'])) {
                $detail_link_settings = $settings['general']['detail_link'];
                $url = Url::factory($detail_link_settings['url'] ?? 'http://www.test.be');
                if (isset($detail_link_settings['cdbid']) && $detail_link_settings['cdbid'] === 'url') {
                    $url->addPath($preprocessed_event['id']);
                }
                else {
                    $query = $url->getQuery();
                    $query['cdbid'] = $preprocessed_event['id'];
                    $url->setQuery($query);
                }

                $preprocessed_event['detail_link'] = $url->__toString();

            }

            $preprocessed_events[] = $preprocessed_event;

        }

        return $preprocessed_events;
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
            'when_start' => $this->formatDate($event->getStartDate(), $langcode),
            'where' => $event->getLocation() ? $event->getLocation()->getName()[$langcode] ?? null : null,
            'organizer' => $event->getOrganizer() ? $event->getOrganizer()->getName() : null,
            'age_range' => ($event->getTypicalAgeRange() ? $this->formatAgeRange($event->getTypicalAgeRange(), $langcode) : null),
            'themes' => $event->getTermsByDomain('theme'),
            'labels' => $event->getLabels(),
            'vlieg' => $this->checkVliegEvent($event->getTypicalAgeRange(), $event->getLabels()),
            'uitpas' => $event->getOrganizer() ? $this->checkUitpasEvent($event->getOrganizer()->getHiddenLabels()) : false,
        ];

        if (!empty($settings['description']['characters'])) {
            $variables['description'] = substr($variables['description'], 0, $settings['description']['characters']);
        }

        $labels = $event->getLabels();
        $language_icon_keywords = [
            'één taalicoon' => 1,
            'twee taaliconen' => 2,
            'drie taaliconen' => 3,
            'vier taaliconen' => 4,
        ];

        // Search for language keywords. Take the highest value of all items that match..
        $total_language_icons = 0;
        if (!empty($labels)) {
            foreach ($language_icon_keywords as $keyword => $value) {
                if (in_array($keyword, $labels)) {
                    $total_language_icons = $value;
                }
            }
        }

        $variables['language_icons']= '';
        if ($total_language_icons) {
            $variables['language_icons'] = $this->twig->render('widgets/language-icons.html.twig', ['score' => $total_language_icons]);
        }

        if (!empty($variables['labels']) && !empty($settings['labels']['limit_labels']) && $settings['labels']['limit_labels']['enabled']) {
            $allowed_labels = explode(', ', $settings['labels']['limit_labels']['labels']);
            $variables['labels'] = array_intersect($variables['labels'], $allowed_labels);
        }

        return $variables;
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

        $originalLocale = setlocale  (LC_TIME,"0");

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