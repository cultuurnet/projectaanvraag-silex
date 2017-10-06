<?php

namespace CultuurNet\ProjectAanvraag\ShareProxy;

use CultuurNet\ProjectAanvraag\ShareProxy\Controller\ShareProxyController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class ShareProxyControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {

        $app['share_proxy_controller'] = function (Application $app) {
            return new ShareProxyController($app['widget_renderer'], $app['widget_repository'], $app['mongodb'], $app['search_api'], $app['widget_page_deserializer'], $app['twig'], $app['request_stack'], $app['debug']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/event/{cdbid}', 'share_proxy_controller:socialShareProxy');

        return $controllers;
    }
}
