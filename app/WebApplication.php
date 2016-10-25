<?php

namespace CultuurNet\ProjectAanvraag;

use CultuurNet\ProjectAanvraag\Core\Exception\ValidationException;
use CultuurNet\ProjectAanvraag\ErrorHandler\JsonErrorHandler;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeControllerProvider;
use CultuurNet\ProjectAanvraag\Project\ProjectControllerProvider;
use CultuurNet\UiTIDProvider\User\UserControllerProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Silex\Application as SilexApplication;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\HttpFoundation\Request;

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

        // Add custom error handler for json requests.
        if (!$this['debug']) {
            $errorHandler = new JsonErrorHandler();
            $this->error(
                function (ValidationException $e, Request $request) use ($errorHandler) {
                    return $errorHandler->handleValidationExceptions($e, $request);
                }
            );

            $this->error(
                function (\Exception $e, Request $request) use ($errorHandler) {
                    return $errorHandler->handleException($e, $request);
                }
            );
        }
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
        $this->mount('projects', new ProjectControllerProvider());
        $this->mount('integration-types', new IntegrationTypeControllerProvider());

        $this->mount('uitid', new UserControllerProvider());
        $this->mount(
            'culturefeed/oauth',
            new \CultuurNet\UiTIDProvider\Auth\AuthControllerProvider()
        );
    }
}
