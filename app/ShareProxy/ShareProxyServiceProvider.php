<?php

namespace CultuurNet\ProjectAanvraag\ShareProxy;

use CultuurNet\ProjectAanvraag\ShareProxy\Converter\OfferCbidConverter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provides share proxy related services.
 */
class ShareProxyServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['offer_cbid_converter'] = function (Container $pimple) {
            return new OfferCbidConverter($pimple['search_api']);
        };
    }
}
