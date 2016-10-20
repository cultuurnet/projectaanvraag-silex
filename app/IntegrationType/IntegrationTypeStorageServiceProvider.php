<?php

namespace CultuurNet\ProjectAanvraag\IntegrationType;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class IntegrationTypeStorageServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * @param \Pimple\Container $app
     */
    public function register(Container $app)
    {
        $app['integration_types.storage'] = function (Container $app) {
            return new IntegrationTypeStorage($this->file);
        };
    }
}
