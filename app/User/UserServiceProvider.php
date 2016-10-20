<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\UiTIDProvider\User\CachedUserService;
use CultuurNet\UiTIDProvider\User\UserServiceProvider as UiTIDUserServiceProvider;
use Pimple\Container;

class UserServiceProvider extends UiTIDUserServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $pimple)
    {
        parent::register($pimple);

        // Replace the User service
        $pimple['uitid_user_service'] = function (Container $pimple) {
            $service = new CachedUserService(
                new UserService($pimple['culturefeed'])
            );

            $currentUser = $pimple['uitid_user_session_data_complete'];
            if (!is_null($currentUser)) {
                $service->cacheUser($currentUser);
            }

            return $service;
        };
    }
}
