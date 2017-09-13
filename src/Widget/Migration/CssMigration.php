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

            // TODO

        }

        $this->css = $css;
    }

}
