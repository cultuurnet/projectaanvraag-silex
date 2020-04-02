<?php

namespace CultuurNet\ProjectAanvraag\Security;

use Pimple\Container;
use CultuurNet\UiTIDProvider\Security\UiTIDSecurityServiceProvider as CultuurNetUiTIDSecurityServiceProvider;

class UiTIDSecurityServiceProvider extends CultuurNetUiTIDSecurityServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $pimple)
    {
        parent::register($pimple);

        $pimple['uitid_firewall_user_provider'] = function (Container $pimple) {
            return new UiTIDUserProvider($pimple['uitid_user_service']);
        };
    }
}
