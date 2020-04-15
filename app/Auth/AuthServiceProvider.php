<?php

namespace CultuurNet\UiTIDProvider\Auth;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AuthServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritdoc
     */
    public function register(Container $pimple)
    {
        $pimple['uitid_auth_service'] = function (Container $pimple) {
            return new AuthService(
                $pimple['culturefeed.endpoint'],
                $pimple['culturefeed_consumer_credentials'],
                $pimple['session']
            );
        };
    }
}
