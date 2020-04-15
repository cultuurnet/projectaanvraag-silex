<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\UiTIDProvider\User\CachedUserService;
use CultuurNet\UiTIDProvider\User\UserService;
use CultuurNet\UiTIDProvider\User\UserSessionService;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class UserServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['uitid_user_service'] = function (Container $pimple) {
            $service = new CachedUserService(new UserService($pimple['culturefeed']));

            $currentUser = $pimple['uitid_user_session_data_complete'];
            if (!is_null($currentUser)) {
                $service->cacheUser($currentUser);
            }

            return $service;
        };

        $pimple['uitid_user_session_service'] = function (Container $pimple) {
            return new UserSessionService($pimple['session']);
        };

        $pimple['uitid_user_session_data'] = function (Container $pimple) {
            /* @var UserSessionService $userSessionService */
            $userSessionService = $pimple['uitid_user_session_service'];
            return $userSessionService->getMinimalUserInfo();
        };

        $pimple['uitid_user_session_data_complete'] = function (Container $pimple) {
            /* @var UserSessionService $userSessionService */
            $userSessionService = $pimple['uitid_user_session_service'];
            return $userSessionService->getUser();
        };

        $pimple['uitid_user'] = function (Container $pimple) {
            if (!is_null($pimple['uitid_user_session_data_complete'])) {
                return $pimple['uitid_user_session_data_complete'];
            }

            /* @var \Cultuurnet\Auth\User $userSessionData */
            $userSessionData = $pimple['uitid_user_session_data'];
            if (is_null($userSessionData)) {
                return null;
            }

            /* @var UserService $userService */
            $userService = $pimple['uitid_user_service'];
            $user = $userService->getUser($userSessionData->getId());

            /* @var UserSessionService $userSessionService */
            $userSessionService = $pimple['uitid_user_session_service'];
            $userSessionService->setUser($user);

            return $user;
        };
    }
}
