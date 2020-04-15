<?php

namespace CultuurNet\ProjectAanvraag;

use CultuurNet\ProjectAanvraag\Core\HomeControllerProvider;
use CultuurNet\ProjectAanvraag\Core\Exception\ValidationException;
use CultuurNet\ProjectAanvraag\Coupon\CouponControllerProvider;
use CultuurNet\ProjectAanvraag\ErrorHandler\JsonErrorHandler;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeControllerProvider;
use CultuurNet\ProjectAanvraag\Project\ProjectControllerProvider;
use CultuurNet\ProjectAanvraag\Security\UiTIDSecurityServiceProvider;
use CultuurNet\ProjectAanvraag\Voter\ImportVoter;
use CultuurNet\ProjectAanvraag\Voter\ProjectVoter;
use CultuurNet\ProjectAanvraag\Widget\WidgetAPIControllerProvider;
use CultuurNet\ProjectAanvraag\Widget\WidgetControllerProvider;
use CultuurNet\ProjectAanvraag\Upload\UploadControllerProvider;
use CultuurNet\ProjectAanvraag\ShareProxy\ShareProxyControllerProvider;
use CultuurNet\ProjectAanvraag\Security\MultiPathRequestMatcher;
use CultuurNet\ProjectAanvraag\Security\Path;
use CultuurNet\ProjectAanvraag\User\UserControllerProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Silex\Application as SilexApplication;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

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

            // Access denied exceptions (403)
            $this->error(
                function (AccessDeniedHttpException $e, Request $request) use ($errorHandler) {
                    return $errorHandler->handleAccessDeniedExceptions($e, $request);
                }
            );

            // Validation exceptions (400)
            $this->error(
                function (ValidationException $e, Request $request) use ($errorHandler) {
                    return $errorHandler->handleValidationExceptions($e, $request);
                }
            );

            // Not found exceptions (404)
            $this->error(
                function (NotFoundHttpException $e, Request $request) use ($errorHandler) {
                    return $errorHandler->handleNotFoundExceptions($e, $request);
                }
            );

            // General exceptions (500)
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
                'cors.exposeHeaders' => 'Authorization',
            ]
        );
        $this->register(new SessionServiceProvider());
        $this->register(new RoutingServiceProvider());

        // Security
        $this->register(new SecurityServiceProvider());
        $this->register(new UiTIDSecurityServiceProvider());

        $this['security.firewalls'] = [
            'unsecured' => [
                'pattern' => MultiPathRequestMatcher::fromPaths(
                    [
                        new Path('^/$', 'GET'),
                        new Path('^/culturefeed/oauth', 'GET'),
                        new Path('^/integration-types', 'GET'),
                        new Path('^/widgets/layout', 'GET'),
                        new Path('^/widgets/api/render', 'GET'),
                        new Path('^/widgets/autocomplete/regions', 'GET'),
                        new Path('^/event/', 'GET'),
                        new Path('^/upload', 'POST'),
                        new Path('^.*$', 'OPTIONS'),
                    ]
                ),
            ],
            'secured' => [
                'pattern' => '^.*$',
                'uitid' => true,
                'users' => $this['uitid_firewall_user_provider'],
            ],
        ];

        $this['security.voters'] = function () {
            return [
                // Default Silex voters
                new RoleHierarchyVoter(new RoleHierarchy($this['security.role_hierarchy'])),
                new AuthenticatedVoter($this['security.trust_resolver']),

                // Custom voters
                new ProjectVoter(),
                new ImportVoter(),
            ];
        };

        // HTTP cache
        $this->register(
            new HttpCacheServiceProvider(),
            [
                'http_cache.cache_dir' => $this['cache_directory'] . '/http',
                'http_cache.esi'       => null,
            ]
        );
    }

    /**
     * Register all controllers.
     */
    protected function mountControllers()
    {
        $this->mount('project', new ProjectControllerProvider());
        $this->mount('integration-types', new IntegrationTypeControllerProvider());
        $this->mount('coupons', new CouponControllerProvider());

        $this->mount('uitid', new UserControllerProvider());
        $this->mount(
            'culturefeed/oauth',
            new \CultuurNet\ProjectAanvraag\Auth\AuthControllerProvider()
        );

        $this->mount(
            'widgets',
            new WidgetAPIControllerProvider()
        );

        $this->mount(
            'widgets',
            new WidgetControllerProvider()
        );

        $this->mount(
            null,
            new UploadControllerProvider()
        );

        $this->mount(
            null,
            new HomeControllerProvider()
        );

        $this->mount(
            null,
            new ShareProxyControllerProvider()
        );
    }
}
