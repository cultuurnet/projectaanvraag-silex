<?php

namespace CultuurNet\ProjectAanvraag\Widget\Migration;

/**
 * Class CssMigration
 * @package CultuurNet\ProjectAanvraag\Widget\Migration
 */
class CssMigration
{
    /**
     * @var string
     */
    private $css;

    /**
     * @return string
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * @param string $css
     */
    public function setCss($css)
    {
        $this->css = $css;
    }

    /**
     * CssMigration constructor.
     *
     * @param array $blocks
     */
    public function __construct(array $blocks)
    {
        $css = '';

        foreach ($blocks as $block) {
            // Fix possible malformed serialized settings.
            $block['settings'] = utf8_encode($block['settings']);
            $block['settings'] = str_replace(["\r", "\n", "\t"], "", $block['settings']);
            // This fixes issues with CSS in settings messing with the character count (for the replace callback).
            $block['settings'] = preg_replace('/";(?!\w:|\}|N)/', '" ;', $block['settings']);
            // Fix character counts in serialized string.
            $block['settings'] = preg_replace_callback(
                '!s:(\d+):"(.*?)";!s',
                function ($m) {
                    $len = strlen($m[2]);
                    $result = "s:$len:\"{$m[2]}\";";
                    return $result;
                },
                $block['settings']
            );
            $settings = ($block['settings'] != null && $block['settings'] != '' ? unserialize($block['settings']) : []);

            /*
             * The freeform CSS values are difficult to place under specific control contexts, so for now
             * these are just added as is to the combined CSS.
             */

            // Root CSS.
            $css .= (isset($settings['css']) ? $settings['css'] . "\n" : '');
            $css .= (isset($settings['style']) ? $this->convertStyleConfigToCss($settings['style'], 'cultuurnet-widget') : '');

            // Controls CSS.
            $css .= (isset($settings['control_header']) ? $this->getControlCss($settings['control_header'], 'control_header') : '');
            $css .= (isset($settings['control_footer']) ? $this->getControlCss($settings['control_footer'], 'control_footer') : '');
            $css .= (isset($settings['control_what']) ? $this->getControlCss($settings['control_what'], 'control_what') : '');
            $css .= (isset($settings['control_where']) ? $this->getControlCss($settings['control_where'], 'control_where') : '');
            $css .= (isset($settings['control_when']) ? $this->getControlCss($settings['control_when'], 'control_when') : '');
            $css .= (isset($settings['control_results']) ? $this->getControlCss($settings['control_results'], 'control_results') : '');
        }

        $this->css = rtrim($css, "\n");
    }

    /**
     * Helper function to make retrieving control CSS code more generic.
     *
     * @param $settings
     * @param $control
     * @return string
     */
    protected function getControlCss($settings, $control)
    {
        $css = '';
        $css .= (isset($settings['css']) ? $settings['css'] : '');

        $wrapperClass = 'cultuurnet-' . str_replace('_', '-', $control);

        $css .= (isset($settings['style']) ? $this->convertStyleConfigToCss($settings['style'], $wrapperClass) . "\n" : '');
        return "$css\n";
    }

    /**
     * Check every style parameter and add a corresponding CSS line if filled in.
     *
     * @param $config
     * @param $wrapperClass
     * @return string
     */
    protected function convertStyleConfigToCss($config, $wrapperClass)
    {
        $css = '';

        // Font.
        $fontFamily = $config['font']['family'];
        $css .= ($fontFamily != '' ? "font-family: $fontFamily;\n" : '');
        $fontSize = $config['font']['size'];
        $css .= ($fontSize != '' ? "font-size: $fontSize;\n" : '');
        $fontColor = $config['font']['color'];
        $css .= ($fontColor != '' ? "font-color: $fontColor;\n" : '');
        $fontAlignment = $config['font']['alignment'];
        $css .= ($fontAlignment != '' ? "text-align: $fontAlignment;\n" : '');

        $fontStyleBold = $config['font']['style']['bold'];
        $css .= ($fontStyleBold ? "font-weight: bold;\n" : '');
        $fontStyleItalic = $config['font']['style']['italic'];
        $css .= ($fontStyleItalic ? "font-style: italic;\n" : '');
        $fontStyleUnderline = $config['font']['style']['underline'];
        $css .= ($fontStyleUnderline ? "text-decoration: underline;\n" : '');

        // Border.
        $borderTop = $config['border']['top'];
        $css .= ($borderTop != '' ? "border-top: $borderTop;\n" : '');
        $borderRight = $config['border']['right'];
        $css .= ($borderRight != '' ? "border-right: $borderRight;\n" : '');
        $borderBottom = $config['border']['bottom'];
        $css .= ($borderBottom != '' ? "border-bottom: $borderBottom;\n" : '');
        $borderLeft = $config['border']['left'];
        $css .= ($borderLeft != '' ? "border-left: $borderLeft;\n" : '');
        $borderColor = $config['border']['color'];
        $css .= ($borderColor != '' ? "border-color: $borderColor;\n" : '');
        $borderStyle = $config['border']['style'];
        $css .= ($borderStyle != '' ? "border-style: $borderStyle;\n" : '');

        // Background.
        $backgroundUrl = ($config['background']['url'] ? 'url("' . $config['background']['url'] . '")' : '');
        $backgroundRepeat = (isset($config['background']['repeat']) ? $config['background']['repeat'] : 'repeat');
        $backgroundColor = (isset($config['background']['color']) ? $config['background']['color'] : '');
        $background = trim("$backgroundColor $backgroundUrl $backgroundRepeat", ' ');
        $css .= ($background != '' ? "background: $background;\n" : '');

        // Margin.
        $marginTop = $config['margin']['top'];
        $css .= ($marginTop != '' ? "margin-top: $marginTop;\n" : '');
        $marginRight = $config['margin']['right'];
        $css .= ($marginRight != '' ? "margin-right: $marginRight;\n" : '');
        $marginBottom = $config['margin']['bottom'];
        $css .= ($marginBottom != '' ? "margin-bottom: $marginBottom;\n" : '');
        $marginLeft = $config['margin']['left'];
        $css .= ($marginLeft != '' ? "margin-left: $marginLeft;\n" : '');

        // Padding.
        $paddingTop = $config['padding']['top'];
        $css .= ($paddingTop != '' ? "padding-top: $paddingTop;\n" : '');
        $paddingRight = $config['padding']['right'];
        $css .= ($paddingRight != '' ? "padding-right: $paddingRight;\n" : '');
        $paddingBottom = $config['padding']['bottom'];
        $css .= ($paddingBottom != '' ? "padding-bottom: $paddingBottom;\n" : '');
        $paddingLeft = $config['padding']['left'];
        $css .= ($paddingLeft != '' ? "padding-left: $paddingLeft;\n" : '');

        $css = ($css != '' ? ".$wrapperClass {\n $css \n}\n" : '');

        // Add possible freestyle CSS from style config.
        $css .= ($config['css'] ? $config['css'] . "\n" : '');
        return "$css";
    }
}
