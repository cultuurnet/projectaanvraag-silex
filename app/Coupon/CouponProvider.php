<?php

namespace CultuurNet\ProjectAanvraag\Coupon;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provides coupon related services.
 */
class CouponProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register(Container $pimple)
    {

        $pimple['coupon_repository'] = function (Container $pimple) {
            return $pimple['orm.em']->getRepository('ProjectAanvraag:Coupon');
        };

        $pimple['coupon_importer'] = function (Container $pimple) {
            return new CouponImporter($pimple['coupon_repository'], $pimple['orm.em']);
        };

        $pimple['coupon_validator'] = function (Container $pimple) {
            return new CouponValidator($pimple['coupon_repository']);
        };
    }
}
