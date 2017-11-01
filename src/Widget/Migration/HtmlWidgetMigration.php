<?php

namespace CultuurNet\ProjectAanvraag\Widget\Migration;

/**
 * Class HtmlWidgetMigration
 * @package CultuurNet\ProjectAanvraag\Widget\Migration
 */
class HtmlWidgetMigration extends WidgetMigration
{
    /**
     * WidgetMigration constructor.
     *
     * @param $legacySettings
     */
    public function __construct($legacySettings)
    {
        $type = 'html';

        $settings = [];
        if (!empty($legacySettings['html'])) {
            $settings['content']['body'] = $legacySettings['html'];
        }

        parent::__construct($this->extendWithGenericSettings($legacySettings, $settings), $type);
    }
}
