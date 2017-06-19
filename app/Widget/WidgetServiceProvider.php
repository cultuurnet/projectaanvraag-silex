<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\Core\Schema\DatabaseSchemaInstaller;
use CultuurNet\ProjectAanvraag\Project\Schema\ProjectSchemaConfigurator;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provides widget related services.
 */
class WidgetServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register(Container $pimple)
    {

        $pimple['widget_repository'] = function (Container $pimple) {
            return $pimple['mongodbodm.dm']->getRepository(WidgetPageEntity::class);
        };

        $pimple['widget_layout_discovery'] = function (Container $pimple) {
            $discovery = new LayoutDiscovery();
            $discovery->register(__DIR__ . '/../../src/Widget/WidgetLayout', 'CultuurNet\ProjectAanvraag\Widget\WidgetLayout');
            return $discovery;
        };

        $pimple['widget_layout_manager'] = function (Container $pimple) {
            return new WidgetPluginManager($pimple['widget_layout_discovery'], $pimple);
        };

        $pimple['widget_type_discovery'] = function (Container $pimple) {
            $discovery = new WidgetTypeDiscovery();
            $discovery->register(__DIR__ . '/../../src/Widget/WidgetType', 'CultuurNet\ProjectAanvraag\Widget\WidgetType');
            return $discovery;
        };

        $pimple['widget_type_manager'] = function (Container $pimple) {
            return new WidgetPluginManager($pimple['widget_type_discovery'], $pimple);
        };
    }
}
