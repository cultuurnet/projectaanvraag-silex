<?php

namespace CultuurNet\ProjectAanvraag\User;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class UserRoleServiceProvider implements ServiceProviderInterface
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
        $app['user_role.storage'] = function (Container $app) {
            return new UserRoleStorage($this->file);
        };
    }
}
