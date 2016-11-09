<?php

namespace CultuurNet\ProjectAanvraag\Coupon;

use CultuurNet\ProjectAanvraag\Coupon\Controller\CouponController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

/**
 * Provides controllers for coupons.
 */
class CouponControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['coupon_controller'] = function (Application $app) {
            return new CouponController($app['coupon_importer'], $app['security.authorization_checker']);
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $controllers->get('/', 'coupon_controller:importCoupons');
        $controllers->post('/', 'coupon_controller:importCoupons');

        return $controllers;
    }
}
