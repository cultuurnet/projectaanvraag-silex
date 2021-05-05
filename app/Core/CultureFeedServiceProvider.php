<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\UiTIDProvider\CultureFeed\CultureFeedServiceProvider as CultureFeedServiceProviderOriginal;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provides all services for the message bus.
 */
class CultureFeedServiceProvider extends CultureFeedServiceProviderOriginal implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // Register live.
        parent::register($pimple);

        $pimple['culturefeed_test'] = function (Container $pimple) {
            return new \CultureFeed($pimple['culturefeed_test_oauth_client']);
        };

        $pimple['culturefeed_test_oauth_client'] = function (Container $pimple) {
            $oauthClient = new \CultureFeed_DefaultOAuthClient(
                $pimple['culturefeed_test.consumer.key'],
                $pimple['culturefeed_test.consumer.secret']
            );
            $oauthClient->setEndpoint($pimple['culturefeed_test.endpoint']);

            return $oauthClient;
        };
    }
}
