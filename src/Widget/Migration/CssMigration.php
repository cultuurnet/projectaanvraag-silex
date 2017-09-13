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

    public function __construct(array $blocks)
    {
        $css = '';

        foreach ($blocks as $block) {
            $settings = unserialize($block['settings']);

            /*
             * TODO:
             * The freeform CSS values are difficult to place under specific control contexts, so for now
             * these are just added as is to the combined CSS.
             */

            // Root CSS
            $css .= ($settings['css'] ? $settings['css'] : '');
            $css .= (isset($settings['style']) ? $this->convertStyleConfigToCss($settings['style'], 'cultuurnet-widget') : '');


        }

        $this->css = $css;
    }

    protected function convertStyleConfigToCss($config, $wrapperClass) {
        $css = '';

        // font
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

        // border
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

        // background
        $backgroundUrl = ($config['background']['url'] ? 'url("' . $config['background']['url'] . '")' : '');
        $backgroundRepeat = $config['background']['repeat'];
        $backgroundColor = $config['background']['color'];
        $background = trim("$backgroundColor $backgroundUrl $backgroundRepeat", ' ');
        $css .= ($background != '' ? "background: $background;\n" : '');

        // margin
        $marginTop = $config['margin']['top'];
        $css .= ($marginTop != '' ? "margin-top: $marginTop;\n" : '');
        $marginRight = $config['margin']['right'];
        $css .= ($marginRight != '' ? "margin-right: $marginRight;\n" : '');
        $marginBottom = $config['margin']['bottom'];
        $css .= ($marginBottom != '' ? "margin-bottom: $marginBottom;\n" : '');
        $marginLeft = $config['margin']['left'];
        $css .= ($marginLeft != '' ? "margin-left: $marginLeft;\n" : '');

        // padding
        $paddingTop = $config['padding']['top'];
        $css .= ($paddingTop != '' ? "padding-top: $paddingTop;\n" : '');
        $paddingRight = $config['padding']['right'];
        $css .= ($paddingRight != '' ? "padding-right: $paddingRight;\n" : '');
        $paddingBottom = $config['padding']['bottom'];
        $css .= ($paddingBottom != '' ? "padding-bottom: $paddingBottom;\n" : '');
        $paddingLeft = $config['padding']['left'];
        $css .= ($paddingLeft != '' ? "padding-left: $paddingLeft;\n" : '');

        $css = ($css != '' ? ".$wrapperClass {\n $css \n}" : '');

        // Add possible freestyle CSS from style config.
        $css .= ($config['css'] ? $config['css'] : '');
        return $css;
    }

}
