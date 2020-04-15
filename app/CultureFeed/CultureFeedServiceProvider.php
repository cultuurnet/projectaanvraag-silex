<?php

namespace CultuurNet\UiTIDProvider\CultureFeed;

use CultuurNet\Auth\ConsumerCredentials;
use CultuurNet\Auth\TokenCredentials;
use CultuurNet\UiTIDProvider\User\UserSessionService;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CultureFeedServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritdoc
     */
    public function register(Container $pimple)
    {
        $pimple['culturefeed_token_credentials'] = function (Container $pimple) {
            /* @var UserSessionService $userSessionService */
            $userSessionService = $pimple['uitid_user_session_service'];
            $user = $userSessionService->getMinimalUserInfo();
            if (!is_null($user)) {
                return $user->getTokenCredentials();
            } else {
                return null;
            }
        };

        $pimple['culturefeed_consumer_credentials'] = function (Container $pimple) {
            return new ConsumerCredentials(
                $pimple['culturefeed.consumer.key'],
                $pimple['culturefeed.consumer.secret']
            );
        };

        $pimple['culturefeed'] = function (Container $pimple) {
            return new \CultureFeed($pimple['culturefeed_oauth_client']);
        };

        $pimple['culturefeed_oauth_client'] = function (Container $pimple) {
            /* @var ConsumerCredentials $consumerCredentials */
            $consumerCredentials = $pimple['culturefeed_consumer_credentials'];

            /* @var TokenCredentials $tokenCredentials */
            $tokenCredentials = $pimple['culturefeed_token_credentials'];

            $userCredentialsToken = null;
            $userCredentialsSecret = null;
            if ($tokenCredentials) {
                $userCredentialsToken = $tokenCredentials->getToken();
                $userCredentialsSecret = $tokenCredentials->getSecret();
            }

            $oauthClient = new \CultureFeed_DefaultOAuthClient(
                $consumerCredentials->getKey(),
                $consumerCredentials->getSecret(),
                $userCredentialsToken,
                $userCredentialsSecret
            );
            $oauthClient->setEndpoint($pimple['culturefeed.endpoint']);

            return $oauthClient;
        };
    }
}
