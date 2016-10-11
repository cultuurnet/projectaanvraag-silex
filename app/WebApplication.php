<?php

namespace CultuurNet\ProjectAanvraag;

use CultuurNet\ProjectAanvraag\Project\ProjectControllerProvider;
use Silex\Application as SilexApplication;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;

/**
 * Application class for the projectaanvraag app: web version.
 */
class WebApplication extends ApplicationBase
{

    public function __construct()
    {
        parent::__construct();
        $this->mountControllers();
    }

    /**
     * Register all service providers.
     */
    protected function registerProviders()
    {

        parent::registerProviders();

        $this->register(new ServiceControllerServiceProvider());
        $this->register(new SessionServiceProvider());
        $this->register(new RoutingServiceProvider());
    }

    /**
     * Register all controllers.
     */
    protected function mountControllers()
    {
        $this->mount('/projects', new ProjectControllerProvider());

        $this->mount(
            'culturefeed/oauth',
            new \CultuurNet\UiTIDProvider\Auth\AuthControllerProvider()
        );
    }
}
