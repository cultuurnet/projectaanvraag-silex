<?php

namespace CultuurNet\ProjectAanvraag\IntegrationTypes;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class IntegrationTypesStorageServiceProvider implements ServiceProviderInterface
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
            return new IntegrationTypesStorage($this->file);
        };
    }
}
