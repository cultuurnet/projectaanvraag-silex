<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\ContainerFactoryPluginInterface;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\Twig\TwigPreprocessor;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPager;
use CultuurNet\SearchV3\ValueObjects\FacetResults;
use CultuurNet\SearchV3\ValueObjects\FacetResult;
use Pimple\Container;

class WidgetTypeBase implements WidgetTypeInterface, ContainerFactoryPluginInterface
{

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var TwigPreprocessor
     */
    protected $twigPreprocessor;

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
     * Index of position in the page.
     * @var int
     */
    protected $index;

    /**
     * WidgetTypeBase constructor.
     * @param array $pluginDefinition
     * @param array $configuration
     * @param bool $cleanup
     * @param \Twig_Environment $twig
     * @param TwigPreprocessor $twigPreprocessor
     * @param RendererInterface $renderer
     */
    public function __construct(array $pluginDefinition, array $configuration, bool $cleanup, \Twig_Environment $twig, TwigPreprocessor $twigPreprocessor, RendererInterface $renderer)
    {
        $this->pluginDefinition = $pluginDefinition;
        $this->renderer = $renderer;
        $this->twigPreprocessor = $twigPreprocessor;
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
            $configuration,
            $cleanup,
            $container['twig'],
            $container['widget_twig_preprocessor'],
            $container['widget_renderer']
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
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param int $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * Clean URL query parameters from question marks.
     *
     * @param $params
     * @return array
     */
    protected function cleanUrlQueryParams($params)
    {
        if (!empty($params)) {
            foreach ($params as $key => $param) {
                // Check key for question mark.
                if (substr($key, 0, 1) == '?') {
                    // Trim question mark.
                    $trimmedKey = ltrim($key, '?');
                    // Replace key.
                    $params[$trimmedKey] = $param;
                    unset($params[$key]);
                }
            }
        }
        return $params;
    }

    /**
     * Convert date type parameter to ISO-8601 date range.
     *
     * @param $dateType
     * @return string
     */
    protected function convertDateTypeToDateRange($dateType)
    {
        // Determine start & end date.
        $cetTimezone = new \DateTimeZone('CET');
        $label = '';
        switch ($dateType) {
            case 'today':
                $startDate = new \DateTime('now', $cetTimezone);
                $endDate = new \DateTime('now', $cetTimezone);
                $label = 'Vandaag';
                break;

            case 'tomorrow':
                $startDate = new \DateTime('+1 day', $cetTimezone);
                $endDate = new \DateTime('+1 day', $cetTimezone);
                $label = 'Morgen';
                break;

            case 'thisweekend':
                $now = new \DateTime();
                // Check if we are already in a weekend or not.
                if ($now->format('N') == 6) {
                    $startDate = $now;
                    $endDate = new \DateTime('next Sunday', $cetTimezone);
                } elseif ($now->format('N') == 7) {
                    $startDate = $now;
                    $endDate = $now;
                } else {
                    $startDate = new \DateTime('next Saturday', $cetTimezone);
                    $endDate = new \DateTime('next Sunday', $cetTimezone);
                }

                $label = 'Dit weekend';
                break;

            case 'next7days':
                $startDate = new \DateTime('now', $cetTimezone);
                $endDate = new \DateTime('+7 days', $cetTimezone);
                $label = 'Volgende 7 dagen';
                break;

            case 'next14days':
                $startDate = new \DateTime('now', $cetTimezone);
                $endDate = new \DateTime('+14 days', $cetTimezone);
                $label = 'Volgende 14 dagen';
                break;

            case 'next30days':
                $startDate = new \DateTime('now', $cetTimezone);
                $endDate = new \DateTime('+30 days', $cetTimezone);
                $label = 'Volgende 30 dagen';
                break;

            default:
                return;
        }

        // Set time and format to ISO-8601 and set correct start and end hour.
        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);

        return [
            'query' => '[' . $startDate->format('c') . ' TO ' . $endDate->format('c') . ']',
            'label' => $label,
        ];
    }

    /**
     * Return a WidgetPager object for the given data.
     *
     * @param int $itemsPerPage
     * @param int $totalItems
     * @param int $pageIndex
     * @return WidgetPager
     */
    protected function retrievePagerData(int $itemsPerPage, int $totalItems, int $pageIndex)
    {
        // Determine number of pages.
        $pages = ceil($totalItems / $itemsPerPage);

        return new WidgetPager($pages, $pageIndex, $itemsPerPage);
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

    /**
     * Filter the given string for XSS.
     *
     * @param $string
     * @param $allowedTags
     * @return mixed
     */
    protected function filterXss(
        $string,
        $allowedTags = [
            'a',
            'p',
            'img',
            'em',
            'strong',
            'cite',
            'blockquote',
            'code',
            'ul',
            'ol',
            'li',
            'dl',
            'dt',
            'dd',
            'table',
            'tbody',
            'td',
            'tr',
        ]
    ) {
        // Remove NULL characters (ignored by some browsers).
        $string = str_replace(chr(0), '', $string);
        // Remove Netscape 4 JS entities.
        $string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);

        // Defuse all HTML entities.
        $string = str_replace('&', '&amp;', $string);
        // Change back only well-formed entities in our whitelist:
        // Decimal numeric entities.
        $string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
        // Hexadecimal numeric entities.
        $string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);
        // Named entities.
        $string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);
        $htmlTags = array_flip($allowedTags);
        // Late static binding does not work inside anonymous functions.
        $class = get_called_class();
        $splitter = function ($matches) use ($htmlTags, $class) {
            return $class::filterXssSplit($matches[1], $htmlTags, $class);
        };
        // Strip any tags that are not in the whitelist.
        return preg_replace_callback(
            '%
      (
      <(?=[^a-zA-Z!/])  # a lone <
      |                 # or
      <!--.*?-->        # a comment
      |                 # or
      <[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
      |                 # or
      >                 # just a >
      )%x',
            $splitter,
            $string
        );
    }

    /**
     * Processes an HTML tag.
     *
     * @param string $string
     *   The HTML tag to process.
     * @param array $htmlTags
     *   An array where the keys are the allowed tags and the values are not
     *   used.
     * @param string $class
     *   The called class. This method is called from an anonymous function which
     *   breaks late static binding. See https://bugs.php.net/bug.php?id=66622 for
     *   more information.
     *
     * @return string
     *   If the element isn't allowed, an empty string. Otherwise, the cleaned up
     *   version of the HTML element.
     */
    protected static function filterXssSplit($string, $htmlTags, $class)
    {
        if (substr($string, 0, 1) != '<') {
            // We matched a lone ">" character.
            return '&gt;';
        } elseif (strlen($string) == 1) {
            // We matched a lone "<" character.
            return '&lt;';
        }

        if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9\-]+)\s*([^>]*)>?|(<!--.*?-->)$%', $string, $matches)) {
            // Seriously malformed.
            return '';
        }
        $slash = trim($matches[1]);
        $elem = &$matches[2];
        $attrlist = &$matches[3];
        $comment = &$matches[4];

        if ($comment) {
            $elem = '!--';
        }

        // When in whitelist mode, an element is disallowed when not listed.
        if (!isset($htmlTags[strtolower($elem)])) {
            return '';
        }

        if ($comment) {
            return $comment;
        }

        if ($slash != '') {
            return "</$elem>";
        }

        // Is there a closing XHTML slash at the end of the attributes?
        $attrlist = preg_replace('%(\s?)/\s*$%', '\1', $attrlist, -1, $count);
        $xhtmlSlash = $count ? ' /' : '';

        // Clean up attributes.
        $attr2 = implode(' ', $class::filterXssAttributes($attrlist));
        $attr2 = preg_replace('/[<>]/', '', $attr2);
        $attr2 = strlen($attr2) ? ' ' . $attr2 : '';

        return "<$elem$attr2$xhtmlSlash>";
    }

    /**
     * Processes a string of HTML attributes.
     *
     * @param string $attributes
     *   The html attribute to process.
     *
     * @return array
     *   Cleaned up version of the HTML attributes.
     */
    protected static function filterXssAttributes($attributes)
    {
        $attributesArray = [];
        $mode = 0;
        $attributeName = '';
        $skip = false;
        $skipProtocolFiltering = false;

        while (strlen($attributes) != 0) {
            // Was the last operation successful?
            $working = 0;

            switch ($mode) {
                case 0:
                    // Attribute name, href for instance.
                    if (preg_match('/^([-a-zA-Z][-a-zA-Z0-9]*)/', $attributes, $match)) {
                        $attributeName = strtolower($match[1]);
                        $skip = ($attributeName == 'style' || substr($attributeName, 0, 2) == 'on');

                        // Values for attributes of type URI should be filtered for
                        // potentially malicious protocols (for example, an href-attribute
                        // starting with "javascript:"). However, for some non-URI
                        // attributes performing this filtering causes valid and safe data
                        // to be mangled. We prevent this by skipping protocol filtering on
                        // such attributes.
                        // @see \Drupal\Component\Utility\UrlHelper::filterBadProtocol()
                        // @see http://www.w3.org/TR/html4/index/attributes.html
                        $attributeNamePart = substr($attributeName, 0, 5);
                        $skipProtocolFiltering = $attributeNamePart === 'data-' || in_array(
                            $attributeName,
                            [
                                'title',
                                'alt',
                                'rel',
                                'property',
                            ]
                        );

                        $working = $mode = 1;
                        $attributes = preg_replace('/^[-a-zA-Z][-a-zA-Z0-9]*/', '', $attributes);
                    }
                    break;

                case 1:
                    // Equals sign or valueless ("selected").
                    if (preg_match('/^\s*=\s*/', $attributes)) {
                        $working = 1;
                        $mode = 2;
                        $attributes = preg_replace('/^\s*=\s*/', '', $attributes);
                        break;
                    }

                    if (preg_match('/^\s+/', $attributes)) {
                        $working = 1;
                        $mode = 0;
                        if (!$skip) {
                            $attributesArray[] = $attributeName;
                        }
                        $attributes = preg_replace('/^\s+/', '', $attributes);
                    }
                    break;

                case 2:
                    // Attribute value, a URL after href= for instance.
                    if (preg_match('/^"([^"]*)"(\s+|$)/', $attributes, $match)) {
                        $thisval = $match[1];

                        if (!$skip) {
                            $attributesArray[] = "$attributeName=\"$thisval\"";
                        }
                        $working = 1;
                        $mode = 0;
                        $attributes = preg_replace('/^"[^"]*"(\s+|$)/', '', $attributes);
                        break;
                    }

                    if (preg_match("/^'([^']*)'(\s+|$)/", $attributes, $match)) {
                        $thisval = $match[1];

                        if (!$skip) {
                            $attributesArray[] = "$attributeName='$thisval'";
                        }
                        $working = 1;
                        $mode = 0;
                        $attributes = preg_replace("/^'[^']*'(\s+|$)/", '', $attributes);
                        break;
                    }

                    if (preg_match("%^([^\s\"']+)(\s+|$)%", $attributes, $match)) {
                        $thisval = $match[1];

                        if (!$skip) {
                            $attributesArray[] = "$attributeName=\"$thisval\"";
                        }
                        $working = 1;
                        $mode = 0;
                        $attributes = preg_replace("%^[^\s\"']+(\s+|$)%", '', $attributes);
                    }
                    break;
            }

            if ($working == 0) {
                // Not well formed; remove and try again.
                $attributes = preg_replace(
                    '/
          ^
          (
          "[^"]*("|$)     # - a string that starts with a double quote, up until the next double quote or the end of the string
          |               # or
          \'[^\']*(\'|$)| # - a string that starts with a quote, up until the next quote or the end of the string
          |               # or
          \S              # - a non-whitespace character
          )*              # any number of the above three
          \s*             # any number of whitespaces
          /x',
                    '',
                    $attributes
                );
                $mode = 0;
            }
        }

        // The attribute list ends with a valueless attribute like "selected".
        if ($mode == 1 && !$skip) {
            $attributesArray[] = $attributeName;
        }
        return $attributesArray;
    }
}
