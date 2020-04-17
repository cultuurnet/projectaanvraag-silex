<?php

namespace CultuurNet\ProjectAanvraag\Security;

use CultuurNet\ProjectAanvraag\Security\PreflightRequestMatcher;
use CultuurNet\ProjectAanvraag\Security\UiTIDAuthenticator;
use CultuurNet\ProjectAanvraag\Security\UiTIDListener;
use CultuurNet\ProjectAanvraag\Security\UiTIDUserProvider;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class UiTIDSecurityServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['uitid_firewall_user_provider'] = function (Container $pimple) {
            return new UiTIDUserProvider($pimple['uitid_user_service']);
        };

        $pimple['cors_preflight_request_matcher'] = new PreflightRequestMatcher();

        $pimple['security.authentication_provider.uitid._proto'] = $pimple->protect(function () use ($pimple) {
            return function () use ($pimple) {
                return new UiTIDAuthenticator($pimple['uitid_user_service']);
            };
        });

        $pimple['security.authentication_listener.factory.uitid'] = $pimple->protect(
            function ($name, $options) use ($pimple) {
                $pimple['security.authentication_provider.' . $name . '.uitid'] =
                    $pimple['security.authentication_provider.uitid._proto'](
                        $name,
                        $options
                    );

                $pimple['security.authentication_listener.' . $name . '.uitid'] = function () use ($pimple) {
                    return new UiTIDListener(
                        $pimple['security.authentication_manager'],
                        $pimple['security.token_storage'],
                        $pimple['uitid_user_session_service']
                    );
                };

                return array(
                    'security.authentication_provider.' . $name . '.uitid',
                    'security.authentication_listener.' . $name . '.uitid',
                    null,
                    'pre_auth',
                );
            }
        );

        $pimple['uitid_firewall_user_provider'] = function (Container $pimple) {
            return new UiTIDUserProvider($pimple['uitid_user_service']);
        };
    }
}
