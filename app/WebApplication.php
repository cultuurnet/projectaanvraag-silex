<?php

namespace CultuurNet\ProjectAanvraag;

use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeControllerProvider;
use CultuurNet\ProjectAanvraag\Project\ProjectControllerProvider;
use CultuurNet\UiTIDProvider\User\UserControllerProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
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
        $this->after($this['cors']);
    }

    /**
     * Register all service providers.
     */
    protected function registerProviders()
    {

        parent::registerProviders();

        $this->register(new ServiceControllerServiceProvider());
        $this->register(
            new CorsServiceProvider(),
            [
                'cors.allowOrigin' => implode(' ', $this['config']['cors']['origins']),
                'cors.allowCredentials' => true,
            ]
        );
        $this->register(new SessionServiceProvider());
        $this->register(new RoutingServiceProvider());
    }

    /**
     * Register all controllers.
     */
    protected function mountControllers()
    {
        $this->mount('project', new ProjectControllerProvider());
        $this->mount('integration-types', new IntegrationTypeControllerProvider());

        $this->mount('uitid', new UserControllerProvider());
        $this->mount(
            'culturefeed/oauth',
            new \CultuurNet\UiTIDProvider\Auth\AuthControllerProvider()
        );
    }
}
