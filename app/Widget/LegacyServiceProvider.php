<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\CulturefeedHttpGuzzle\HttpClient;
use CultuurNet\ProjectAanvraag\Core\Schema\DatabaseSchemaInstaller;
use Doctrine\ODM\MongoDB\Id\UuidGenerator;
use Guzzle\Http\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class LegacyServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $pimple)
    {

        /**
         * Provides a legacy database connection.
         */
        $pimple['legacy_widgets_db'] = function (Container $pimple) {
            return $pimple['dbs']['legacy_widgets'];
        };
    }
}
