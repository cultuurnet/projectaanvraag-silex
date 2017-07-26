<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\DiscoveryBase;

/**
 * Discovery class for layouts.
 */
class LayoutDiscovery extends DiscoveryBase
{

    protected $namespace = 'CultuurNet\ProjectAanvraag\Widget\Annotation\Layout';

    protected $cache_index = 'annot.widget-layouts';
}
