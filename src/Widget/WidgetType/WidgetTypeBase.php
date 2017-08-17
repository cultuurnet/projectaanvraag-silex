<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\ContainerFactoryPluginInterface;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;
use Pimple\Container;

class WidgetTypeBase implements WidgetTypeInterface, ContainerFactoryPluginInterface
{

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * @var bool
     */
    protected $cleanup;

    /**
     * @var array
     */
    protected $pluginDefinition;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $settings;

    /**
     * LayoutBase constructor.
     *
     * @param array $plugin_definition
     * @param \Twig_Environment $twig
     * @param RendererInterface $renderer
     * @param array $configuration
     * @param bool $cleanup
     */
    public function __construct(array $pluginDefinition, \Twig_Environment $twig, RendererInterface $renderer, array $configuration, bool $cleanup)
    {
        $this->pluginDefinition = $pluginDefinition;
        $this->renderer = $renderer;
        $this->twig = $twig;

        if (isset($configuration['id'])) {
            $this->id = $configuration['id'];
        }

        if (isset($configuration['name'])) {
            $this->name = $configuration['name'];
        }

        $settings = $configuration['settings'] ?? [];
        if ($cleanup) {
            $settings = $this->cleanupConfiguration($settings, $this->pluginDefinition['annotation']->getAllowedSettings());
        }

        $defaultSettings = $this->pluginDefinition['annotation']->getDefaultSettings();
        if (is_array($defaultSettings)) {
            $settings = $this->mergeDefaults($settings, $defaultSettings);
        }

        $this->settings = $settings;
    }

    /**
     * @inheritDoc
     */
    public static function create(Container $container, array $pluginDefinition, array $configuration, bool $cleanup)
    {
        return new static(
            $pluginDefinition,
            $container['twig'],
            $container['widget_renderer'],
            $configuration,
            $cleanup
        );
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->pluginDefinition['annotation']->getId(),
            'settings' => $this->settings,
        ];
    }

    /**
     * Format array of Event objects for sending to a template.
     *
     * @param array $events
     * @param string $langcode
     * @return array
     */
    public function formatEventData(array $events, string $langcode) {
        $formattedEvents = [];

        foreach ($events as $event) {
            $formattedEvents[] = [
                'name' => $event->getName()[$langcode],
                'description' => $event->getDescription()[$langcode],
                'image' => $event->getImage(),
                'when_start' => $this->formatDate($event->getStartDate(), $langcode),
                'where' => $event->getLocation()->getName()[$langcode],
                'organizer' => ($event->getOrganizer() ? $event->getOrganizer()->getName() : null),
                'age_range' => ($event->getTypicalAgeRange() ? $this->formatAgeRange($event->getTypicalAgeRange(), $langcode) : null),
                'themes' => $event->getTermsByDomain('theme'),
                'vlieg' => $this->checkVliegEvent($event->getTypicalAgeRange(), $event->getLabels()),
                'uitpas' => ($event->getOrganizer() ? $this->checkUitpasEvent($event->getOrganizer()->getHiddenLabels()) : false)
            ];
        }

        return $formattedEvents;
    }

    /**
     * Format a datetime object to a specific format.
     *
     * @param \DateTime $datetime
     * @param string $langcode
     * @return string
     */
    protected function formatDate(\DateTime $datetime, string $langcode) {
        // Format date according to language.
        $full_date = '';
        switch ($langcode) {
            case 'nl':
                $date = $this->translateDate($datetime->format('l d F Y'), $langcode);
                $time = $datetime->format('h:i');
                $full_date = "$date om $time uur";
                break;
        }
        return $full_date;
    }

    /**
     * Temporary function to translate a date string to localized values (only NL for now).
     * @todo: Remove this when i18n is properly implemented.
     *
     * @param string $date
     * @param string $langcode
     * @return string
     */
    protected function translateDate($date, $langcode) {
        switch ($langcode) {
            case 'nl':
                return str_replace([
                    'January',
                    'Jan',
                    'February',
                    'Feb',
                    'March',
                    'Mar',
                    'April',
                    'Apr',
                    'May',
                    'June',
                    'Jun',
                    'July',
                    'Jul',
                    'August',
                    'Aug',
                    'September',
                    'Sep',
                    'October',
                    'Oct',
                    'November',
                    'Nov',
                    'December',
                    'Dec',
                    'Sunday',
                    'Sun',
                    'Monday',
                    'Mon',
                    'Tuesday',
                    'Tue',
                    'Wednesday',
                    'Wed',
                    'Thursday',
                    'Thu',
                    'Friday',
                    'Fri',
                    'Saturday',
                    'Sat'
                ],
                [
                    'Januari',
                    'Jan',
                    'Februari',
                    'Feb',
                    'Maart',
                    'Mar',
                    'April',
                    'Apr',
                    'Mei',
                    'Juni',
                    'Jun',
                    'Juli',
                    'Jul',
                    'Augustus',
                    'Aug',
                    'September',
                    'Sep',
                    'Oktober',
                    'Okt',
                    'November',
                    'Nov',
                    'December',
                    'Dec',
                    'Zondag',
                    'Zo',
                    'Maandag',
                    'Ma',
                    'Dinsdag',
                    'Di',
                    'Woensdag',
                    'Wo',
                    'Donderdag',
                    'Do',
                    'Vrijdag',
                    'Vr',
                    'Zaterdag',
                    'Za'
                ],
                $date);
            default:
                return '';
                break;
        }
    }

    /**
     * Format an age range value according to langcode.
     *
     * @param string $range
     * @param string $langcode
     * @return string
     */
    protected function formatAgeRange($range, string $langcode) {
        // Check for empty range values.
        if ($range == '-') {
            return null;
        }
        // Explode range on dash.
        $expl_range = explode('-', $range);

        // Build range string according to language.
        $range_str = '';
        switch ($langcode) {
            case 'nl':
                $range_str = "Vanaf $expl_range[0] jaar tot $expl_range[1] jaar.";
                break;
        }
        return $range_str;
    }

    /**
     * Check if event is considered a "Vlieg" event and return either
     * the minimum age or a boolean value.
     *
     * @param string $range
     * @param array $labels
     * @return bool|string
     */
    protected function checkVliegEvent($range, $labels) {
        // Check age range if there is one.
        if ($range) {
            // Check for empty range values.
            if ($range !== '-') {
                // Explode range on dash.
                $expl_range = explode('-', $range);
                // Check min age and return it if it's lower than 12.
                if ($expl_range[0] < 12) {
                    return "$expl_range[0]+";
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
    protected function checkUitpasEvent($hiddenLabels) {
        // Check for label values containing "Uitpas".
        if ($hiddenLabels) {
            foreach ($hiddenLabels as $label) {
                if (stripos($label, 'uitpas') !== FALSE) {
                    return TRUE;
                }
            }
        }
        return false;
    }

    /**
     * Merge all defaults into the $settings array.
     */
    protected function mergeDefaults($settings, $defaultSettings)
    {

        foreach ($defaultSettings as $id => $defaultSetting) {
            if (!isset($settings[$id])) {
                $settings[$id] = $defaultSetting;
            } elseif (is_array($settings[$id]) && is_array($defaultSetting)) {
                $settings[$id] = $this->mergeDefaults($settings[$id], $defaultSetting);
            }
        }

        return $settings;
    }

    /**
     * Cleanup the configuration.
     */
    protected function cleanupConfiguration($settings, $allowedSettings)
    {

        foreach ($settings as $id => $value) {
            // Unknown property? Remove from settings.
            if (!isset($allowedSettings[$id])) {
                unset($settings[$id]);
            } elseif (is_array($value)) {
                // If property is an array, and allowed setting also. Cleanup the array.
                if (is_array($allowedSettings[$id])) {
                    $settings[$id] = $this->cleanupConfiguration($value, $allowedSettings[$id]);
                } else {
                    // If a class exists for the setting. Clean it up using the class.
                    if (class_exists($allowedSettings[$id])) {
                        $class = $allowedSettings[$id];
                        $settingType = new $class();
                        $settings[$id] = $settingType->cleanup($settings[$id]);
                    } else {
                        // No class exists => invalid property.
                        unset($settings[$id]);
                    }
                }
            } else {
                // Normal value: Cast to the requested format.
                settype($settings[$id], $allowedSettings[$id]);
            }
        }

        return $settings;
    }
}
