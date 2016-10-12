<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\ProjectAanvraag\Core\Schema\DatabaseSchemaInstaller;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CoreProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $pimple)
    {
        $pimple['service_loader'] = $pimple->protect(
            function ($serviceId) use ($pimple) {
                return $pimple[$serviceId];
            }
        );

        $pimple['database.installer'] = function (Container $pimple) {
                return new DatabaseSchemaInstaller($pimple);
        };
    }
}
