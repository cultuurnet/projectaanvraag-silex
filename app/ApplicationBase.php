<?php

namespace CultuurNet\ProjectAanvraag;

use CultuurNet\ProjectAanvraag\Core\CacheProvider;
use CultuurNet\ProjectAanvraag\Core\CoreProvider;
use CultuurNet\ProjectAanvraag\Core\CultureFeedServiceProvider;
use CultuurNet\ProjectAanvraag\Core\KeepAliveDoctrineServiceProvider;
use CultuurNet\ProjectAanvraag\Core\MessageBusProvider;
use CultuurNet\ProjectAanvraag\Core\YamlConfigServiceProvider;
use CultuurNet\ProjectAanvraag\Coupon\CouponProvider;
use CultuurNet\ProjectAanvraag\CssStats\CssStatsServiceProvider;
use CultuurNet\ProjectAanvraag\Goutte\GoutteServiceProvider;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyServiceProvider;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageServiceProvider;
use CultuurNet\ProjectAanvraag\Project\ProjectProvider;
use CultuurNet\ProjectAanvraag\SearchAPI\SearchAPIServiceProvider;
use CultuurNet\ProjectAanvraag\CuratorenAPI\CuratorenAPIServiceProvider;
use CultuurNet\ProjectAanvraag\ArticleLinkerAPI\ArticleLinkerAPIServiceProvider;
use CultuurNet\ProjectAanvraag\User\UserRoleServiceProvider;
use CultuurNet\ProjectAanvraag\User\UserServiceProvider;
use CultuurNet\ProjectAanvraag\Widget\LegacyServiceProvider;
use CultuurNet\ProjectAanvraag\Widget\ODM\Types\PageRows;
use CultuurNet\ProjectAanvraag\Widget\Translation\TranslationTwigExtension;
use CultuurNet\ProjectAanvraag\Widget\WidgetServiceProvider;
use CultuurNet\ProjectAanvraag\ShareProxy\ShareProxyServiceProvider;
use CultuurNet\ProjectAanvraag\WidgetMigration\WidgetMigrationProvider;
use CultuurNet\UiTIDProvider\Auth\AuthServiceProvider;
use DF\DoctrineMongoDb\Silex\Provider\DoctrineMongoDbProvider;
use DF\DoctrineMongoDbOdm\Silex\Provider\DoctrineMongoDbOdmProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Types\Type;
use MongoDB\Client;
use Silex\Application as SilexApplication;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Base Application class for the projectaanvraag application.
 */
class ApplicationBase extends SilexApplication
{

    public function __construct()
    {

        parent::__construct();

        $this['cache_directory'] = __DIR__ . '/../cache';

        // Make sure the cache directory exists.
        if (!file_exists($this['cache_directory'])) {
            mkdir($this['cache_directory']);
        } elseif (!is_writable($this['cache_directory'])) {
            die('The cache directory is not writable');
        }

        // Load the config.
        if (file_exists($this['cache_directory'] . '/config.php')) {
            $this['config'] = require $this['cache_directory'] . '/config.php';
        } else {
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
                'cache.odm_orm' => $this['config']['odm_orm']['cache'],
            ]
        );

        // Translation
        $this['locale'] = $this['config']['locale'] ?? 'nl';
        $this->register(
            new TranslationServiceProvider(),
            array(
                'locale_fallbacks' => array('nl'),
            )
        );
        $this->extend(
            'translator',
            function ($translator, $app) {
                /** @var TranslatorInterface $translator */
                $translator->addLoader('json', new JsonFileLoader());
                $translator->addLoader('yaml', new YamlFileLoader());
                $translator->addResource('yaml', __DIR__ . '/../locales/en.yml', 'en');
                $translator->addResource('yaml', __DIR__ . '/../locales/fr.yml', 'fr');
                $translator->addResource('yaml', __DIR__ . '/../locales/nl.yml', 'nl');
                $translator->addResource('yaml', __DIR__ . '/../locales/eventtype/fr.yml', 'fr', 'eventtype');
                $translator->addResource('yaml', __DIR__ . '/../locales/eventtype/en.yml', 'en', 'eventtype');
                $translator->addResource('yaml', __DIR__ . '/../locales/eventtype/nl.yml', 'nl', 'eventtype');
                $translator->addResource('yaml', __DIR__ . '/../locales/facets/fr.yml', 'fr', 'facets');
                $translator->addResource('yaml', __DIR__ . '/../locales/facets/en.yml', 'en', 'facets');
                $translator->addResource('yaml', __DIR__ . '/../locales/facets/nl.yml', 'nl', 'facets');
                $translator->addResource('yaml', __DIR__ . '/../locales/region/fr.yml', 'fr', 'region');
                $translator->addResource('yaml', __DIR__ . '/../locales/region/en.yml', 'en', 'region');
                $translator->addResource('yaml', __DIR__ . '/../locales/theme/fr.yml', 'fr', 'theme');
                $translator->addResource('yaml', __DIR__ . '/../locales/theme/en.yml', 'en', 'theme');
                $translator->addResource('yaml', __DIR__ . '/../locales/theme/nl.yml', 'nl', 'theme');
                $translator->addResource('yaml', __DIR__ . '/../locales/when/fr.yml', 'fr', 'when');
                $translator->addResource('yaml', __DIR__ . '/../locales/when/nl.yml', 'nl', 'when');
                $translator->addResource('yaml', __DIR__ . '/../locales/when/en.yml', 'en', 'when');
                $translator->addResource('yaml', __DIR__ . '/../locales/language-icons/fr.yml', 'fr', 'language-icons');
                $translator->addResource('yaml', __DIR__ . '/../locales/language-icons/nl.yml', 'nl', 'language-icons');
                $translator->addResource('yaml', __DIR__ . '/../locales/language-icons/en.yml', 'en', 'language-icons');
                return $translator;
            }
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
                'search_api.base_url' => $this['config']['search_api']['live']['base_url'],
                'search_api.api_key' => $this['config']['search_api']['live']['api_key'],
                'search_api_test.base_url' => $this['config']['search_api']['test']['base_url'],
                'search_api_test.api_key' => $this['config']['search_api']['test']['api_key'],
                'search_api.cache.enabled' => $this['config']['search_api']['cache']['enabled'],
                'search_api.cache.backend' => $this['config']['search_api']['cache']['backend'],
                'search_api.cache.ttl' => $this['config']['search_api']['cache']['ttl'],
            ]
        );

        // Curatoren API
        $this->register(
            new CuratorenAPIServiceProvider(),
            [
                'curatoren_api.base_url' => $this['config']['curatoren_api']['live']['base_url'],
                'curatoren_api_test.base_url' => $this['config']['curatoren_api']['test']['base_url'],
                'curatoren_api.cache.enabled' => $this['config']['curatoren_api']['cache']['enabled'],
                'curatoren_api.cache.backend' => $this['config']['curatoren_api']['cache']['backend'],
                'curatoren_api.cache.ttl' => $this['config']['curatoren_api']['cache']['ttl'],
            ]
        );

        // ArticleLinker API
        $this->register(
            new ArticleLinkerAPIServiceProvider(),
            [
                'articlelinker_api.base_url' => $this['config']['articlelinker_api']['live']['base_url'],
                'articlelinker_api_test.base_url' => $this['config']['articlelinker_api']['test']['base_url'],
                'articlelinker_api.cache.enabled' => $this['config']['articlelinker_api']['cache']['enabled'],
                'articlelinker_api.cache.backend' => $this['config']['articlelinker_api']['cache']['backend'],
                'articlelinker_api.cache.ttl' => $this['config']['articlelinker_api']['cache']['ttl'],
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

        // Doctrine DBAL (custom implementation) and ORM.
        $this->register(
            new KeepAliveDoctrineServiceProvider(),
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
        $mongdbConfig = $this['config']['mongodb'];
        $client = new Client(
            'mongodb://' . $mongdbConfig['default']['host'] . ':' . $mongdbConfig['default']['port'],
            [
                [
                    'username' => $mongdbConfig['default']['user'],
                    'password' => $mongdbConfig['default']['password'],
                    'db' => $mongdbConfig['default']['dbname'],
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
        $this->extend(
            'twig',
            function ($twig, $app) {
                $twig->addExtension(new TranslationTwigExtension('nl', $app['translator']));
                return $twig;
            }
        );
        Type::addType('page_rows', PageRows::class);

        $this->register(
            new WidgetServiceProvider(),
            [
                'region_json_location' => $this['config']['search_api']['region-list'],
                'google_tag_manager' => $this['config']['google_tag_manager'],
            ]
        );

        $this->register(new WidgetMigrationProvider());

        $this->register(new MessageBusProvider());

        $this->register(
            new MessageBusProvider(),
            [
                'failed_message_delay' => $this['config']['rabbitmq']['failed_message_delay'],
            ]
        );

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

        // ShareProxy
        $this->register(
            new ShareProxyServiceProvider(),
            []
        );

        // CSS stats
        $this->register(
            new CssStatsServiceProvider(),
            [
                'css_stats.timeout' => $this['config']['css_stats']['timeout'],
                'css_stats.connect_timeout' => $this['config']['css_stats']['connect_timeout'],
            ]
        );
    }
}
