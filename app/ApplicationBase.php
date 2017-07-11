<?php

namespace CultuurNet\ProjectAanvraag;

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
use CultuurNet\ProjectAanvraag\Widget\ODM\Types\PageRows;
use CultuurNet\ProjectAanvraag\Widget\WidgetServiceProvider;
use CultuurNet\UiTIDProvider\Auth\AuthServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;
use DF\DoctrineMongoDb\Silex\Provider\DoctrineMongoDbProvider;
use DF\DoctrineMongoDbOdm\Silex\Provider\DoctrineMongoDbOdmProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ODM\MongoDB\Types\Type;
use MongoDB\Client;
use Silex\Application as SilexApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;

/**
 * Base Application class for the projectaanvraag application.
 */
class ApplicationBase extends SilexApplication
{

    public function __construct()
    {
        parent::__construct();

        // Load the config.
        $this->register(new YamlConfigServiceProvider(__DIR__ . '/../config.yml'));

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
                'search_api.cache.file_system' => $this['config']['search_api_filesystem_cache'],
                'search_api.cache.redis' => $this['config']['redis'],
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
                'db.options' => $this['config']['database'],
            ]
        );

        $this->register(
            new DoctrineOrmServiceProvider(),
            [
                'orm.proxies_dir' => __DIR__. '/../proxies',
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
                'orm.proxies_dir' => __DIR__. '/../proxies',
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

        Type::addType('page_rows', PageRows::class);

        $this->register(new WidgetServiceProvider(), []);

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
