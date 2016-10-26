<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\Auth\ConsumerCredentials;
use CultuurNet\UiTIDProvider\CultureFeed\CultureFeedServiceProvider as CultureFeedServiceProviderOriginal;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provides all services for the message bus.
 */
class CultureFeedServiceProvider extends CultureFeedServiceProviderOriginal  implements ServiceProviderInterface
{

    /**
     * @inheritdoc
     */
    public function register(Container $pimple)
    {
        // Register live.
        parent::register($pimple);

        // Register test.
        $pimple['culturefeed_test_consumer_credentials'] = function (Container $pimple) {
            return new ConsumerCredentials(
                $pimple['culturefeed_test.consumer.key'],
                $pimple['culturefeed_test.consumer.secret']
            );
        };

        $pimple['culturefeed_test'] = function (Container $pimple) {
            return new \CultureFeed($pimple['culturefeed_test_oauth_client']);
        };

        $pimple['culturefeed_test_oauth_client'] = function (Container $pimple) {
            /* @var ConsumerCredentials $consumerCredentials */
            $consumerCredentials = $pimple['culturefeed_test_consumer_credentials'];


            $oauthClient = new \CultureFeed_DefaultOAuthClient(
                $consumerCredentials->getKey(),
                $consumerCredentials->getSecret()
            );
            $oauthClient->setEndpoint($pimple['culturefeed_test.endpoint']);

            return $oauthClient;
        };

    }

}