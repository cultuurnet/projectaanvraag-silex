<?php

namespace CultuurNet\ProjectAanvraag\Widget\Migration;

/**
 * Migrate a facets widget.
 */
class FacetsWidgetWidgetMigration extends WidgetMigration
{
    /**
     * FacetsWidgetWidgetMigration constructor.
     *
     * @param $legacySettings
     */
    public function __construct($legacySettings)
    {
        $type = 'facets';

        $settings = [];

        $settings['filters']['what'] = in_array('what', $legacySettings['control_results']['refinements']['elements']);
        $settings['filters']['where'] = in_array('where', $legacySettings['control_results']['refinements']['elements']);
        $settings['filters']['when'] = in_array('when', $legacySettings['control_results']['refinements']['elements']);

        parent::__construct($settings, $type);
    }
}
