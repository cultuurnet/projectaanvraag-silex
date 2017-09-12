<?php

namespace CultuurNet\ProjectAanvraag;

use CultuurNet\ProjectAanvraag\Core\CacheProvider;
use CultuurNet\ProjectAanvraag\Core\CoreProvider;
use CultuurNet\ProjectAanvraag\Core\CultureFeedServiceProvider;
use CultuurNet\ProjectAanvraag\Core\MessageBusProvider;
use CultuurNet\ProjectAanvraag\Core\RabbitMQEventListenerProvider;
use CultuurNet\ProjectAanvraag\Coupon\CouponProvider;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyServiceProvider;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageServiceProvider;
use CultuurNet\ProjectAanvraag\Project\ProjectProvider;
use CultuurNet\ProjectAanvraag\SearchAPI\SearchAPIServiceProvider;
use CultuurNet\ProjectAanvraag\User\UserRoleServiceProvider;
use CultuurNet\ProjectAanvraag\User\UserServiceProvider;
use CultuurNet\ProjectAanvraag\Widget\LegacyServiceProvider;
use CultuurNet\ProjectAanvraag\Widget\ODM\Types\PageRows;
use CultuurNet\ProjectAanvraag\Widget\WidgetServiceProvider;
use CultuurNet\UiTIDProvider\Auth\AuthServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;
use DF\DoctrineMongoDb\Silex\Provider\DoctrineMongoDbProvider;
use DF\DoctrineMongoDbOdm\Silex\Provider\DoctrineMongoDbOdmProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Types\Type;
use MongoDB\Client;
use Silex\Application as SilexApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TwigServiceProvider;

/**
 * Base Application class for the projectaanvraag application.
 */
class ApplicationBase extends SilexApplication
{

    public function __construct()
    {
        parent::__construct();

        $this['cache_directory'] = __DIR__ . '/../cache';

        // Load the config.
        if (file_exists($this['cache_directory'] . '/config.php')) {
            $this['config'] = require $this['cache_directory'] . '/config.php';
        }
        else {
            $this->register(new YamlConfigServiceProvider(__DIR__ . '/../config.yml'));
            file_put_contents($this['cache_directory'] . '/config.php', '<?php return ' . var_export($this['config'], true) . ';');
        }

        define('WWW_ROOT', realpath(__DIR__ . '/../web'));

        // Enable debug if requested.
        $this['debug'] = $this['config']['debug'] === true;

        /**
         * PHP error reporting
         */
        if ($this['debug']) {
            error_reporting(2147483647);
            ini_set('display_errors', true);
            ini_set('display_startup_errors', true);
        }

        // JMS Autoload
        AnnotationRegistry::registerLoader('class_exists');
        AnnotationRegistry::registerAutoloadNamespace('JMS\Serializer\Annotation', __DIR__ . '/../../vendor/jms/serializer/src');

        $this->registerProviders();
    }

    /**
     * Register all service providers.
     */
    protected function registerProviders()
    {
        $this->register(
            new CacheProvider(),
            [
                'cache.redis' => $this['config']['cache']['redis'],
                'cache.annotations' => $this['config']['annotations']['cache'],
                'cache.odm_orm' => $this['config']['odm_orm']['cache']
            ]
        );

        // Monolog
        $this->register(new MonologServiceProvider());

        // Uitid
        $this->register(
            new CultureFeedServiceProvider(),
            [
                'culturefeed.endpoint' => $this['config']['uitid']['live']['base_url'],
                'culturefeed.consumer.key' => $this['config']['uitid']['live']['consumer']['key'],
                'culturefeed.consumer.secret' => $this['config']['uitid']['live']['consumer']['secret'],
                'culturefeed_test.endpoint' => $this['config']['uitid']['test']['base_url'],
                'culturefeed_test.consumer.key' => $this['config']['uitid']['test']['consumer']['key'],
                'culturefeed_test.consumer.secret' => $this['config']['uitid']['test']['consumer']['secret'],
            ]
        );
        $this->register(new AuthServiceProvider());

        // Search API
        $this->register(
            new SearchAPIServiceProvider(),
            [
                'search_api.base_url' => $this['config']['search_api']['base_url'],
                'search_api.cache.enabled' => $this['config']['search_api']['cache']['enabled'],
                'search_api.cache.backend' => $this['config']['search_api']['cache']['backend'],
                'search_api.cache.ttl' => $this['config']['search_api']['cache']['ttl'],
            ]
        );

        // User and user roles
        $this->register(new UserRoleServiceProvider(__DIR__ . '/../user_roles.yml'));
        $this->register(new UserServiceProvider());

        // Insightly
        $this->register(
            new InsightlyServiceProvider(),
            [
                'insightly.host' => $this['config']['insightly']['host'],
                'insightly.api_key' => $this['config']['insightly']['api_key'],
                'insightly.project_config' => $this['config']['insightly']['project_config'],
            ]
        );

        $this->register(new CoreProvider());

        // Doctrine DBAL and ORM
        $this->register(
            new DoctrineServiceProvider(),
            [
                'dbs.options' => $this['config']['database'],
            ]
        );

        $this->register(
            new DoctrineOrmServiceProvider(),
            [
                'orm.default_cache' => $this['odm_orm_cache'],
                'orm.proxies_dir' => $this['config']['odm_orm']['proxies_dir'],
                'orm.em.options' => [
                    'mappings' => [
                        [
                            'alias' => 'ProjectAanvraag',
                            'type' => 'annotation',
                            'namespace' => 'CultuurNet\ProjectAanvraag\Entity',
                            'path' => __DIR__.'/../src/Entity',
                            'use_simple_annotation_reader' => false,
                        ],
                    ],
                ],
            ]
        );

        // Mongodb.
        $client = new Client(
            'mongodb://localhost:27017',
            [
                [
                    'username' => 'vagrant',
                    'password' => 'vagrant',
                    'db' => 'widgetbeheer',
                ],
            ]
        );

        $this->register(
            new DoctrineMongoDbProvider(),
            [
                "mongodb.options" => [
                    "server" => $client,
                ],
            ]
        );

        $this->register(
            new DoctrineMongoDbOdmProvider(),
            [
                'mongodbodm.default_cache' => $this['odm_orm_cache'],
                'mongodbodm.proxies_dir' => $this['config']['odm_orm']['proxies_dir'],
                'mongodbodm.hydrator_dir' => $this['config']['odm_orm']['hydrator_dir'],
                'mongodbodm.auto_generate_hydrators' => Configuration::AUTOGENERATE_FILE_NOT_EXISTS,
                "mongodbodm.dm.options" => [
                    "database" => "widgets",
                    "mappings" => [
                        [
                            "type" => "annotation",
                            'namespace' => 'CultuurNet\ProjectAanvraag\Widget\Entities',
                            'path' => __DIR__.'/../src/Widget/Entities',
                        ],
                    ],
                ],
            ]
        );

        // Twig
        $this->register(
            new TwigServiceProvider(),
            [
                'twig.path' => __DIR__ . '/../views',
                'twig.options'    => [
                    'cache' => $this['cache_directory'] . '/twig',
                ],
            ]
        );

        Type::addType('page_rows', PageRows::class);

        $this->register(
            new WidgetServiceProvider(),
            []
        );

        $this->register(new LegacyServiceProvider());

        $this->register(new MessageBusProvider());

        // Integration types
        $this->register(new IntegrationTypeStorageServiceProvider(__DIR__ . '/../integration_types.yml'));

        // Project
        $this->register(new ProjectProvider());

        // Coupons.
        $this->register(new CouponProvider());

        // Insightly
        $this->register(
            new InsightlyServiceProvider(),
            [
                'insightly.host' => $this['config']['insightly']['host'],
                'insightly.api_key' => $this['config']['insightly']['api_key'],
            ]
        );
    }
}
