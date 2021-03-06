<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\DiscoveryBase;

/**
 * Discovery class for widget types.
 */
class WidgetTypeDiscovery extends DiscoveryBase
{

    protected $namespace = 'CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType';

    protected $cacheIndex = 'annot.widget-types';
}
