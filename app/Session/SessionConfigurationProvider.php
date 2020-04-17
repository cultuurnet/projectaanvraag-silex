<?php

namespace CultuurNet\ProjectAanvraag\Session;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provides a default session configuration for UiTID projects.
 *
 * Class SessionConfigurationProvider
 * @package CultuurNet\ProjectAanvraag\Session
 */
class SessionConfigurationProvider implements ServiceProviderInterface
{

    /**
     * @inheritdoc
     */
    public function register(Container $pimple)
    {
        /**
         * Keep the session until the browser is closed
         */
        $pimple['session.storage.options'] = [
            'cookie_lifetime' => 0
        ];
    }
}
