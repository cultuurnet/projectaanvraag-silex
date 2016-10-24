<?php

namespace CultuurNet\ProjectAanvraag;

use CultuurNet\ProjectAanvraag\Core\CoreProvider;
use CultuurNet\ProjectAanvraag\Core\MessageBusProvider;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageServiceProvider;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyServiceProvider;
use CultuurNet\ProjectAanvraag\Project\ProjectProvider;
use CultuurNet\ProjectAanvraag\User\UserRoleServiceProvider;
use CultuurNet\ProjectAanvraag\User\UserServiceProvider;
use CultuurNet\UiTIDProvider\Auth\AuthServiceProvider;
use CultuurNet\UiTIDProvider\CultureFeed\CultureFeedServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Silex\Application as SilexApplication;
use Silex\Provider\DoctrineServiceProvider;

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

        $this->registerProviders();
    }

    /**
     * Register all service providers.
     */
    protected function registerProviders()
    {
        // Uitid
        $this->register(
            new CultureFeedServiceProvider(),
            [
                'culturefeed.endpoint' => $this['config']['uitid']['base_url'],
                'culturefeed.consumer.key' => $this['config']['uitid']['consumer']['key'],
                'culturefeed.consumer.secret' => $this['config']['uitid']['consumer']['secret'],
            ]
        );
        $this->register(new AuthServiceProvider());

        // User and user roles
        $this->register(new UserRoleServiceProvider(__DIR__ . '/../user_roles.yml'));
        $this->register(new UserServiceProvider());

        // Insightly
        $this->register(
            new InsightlyServiceProvider(),
            [
                'insightly.host' => $this['config']['insightly']['host'],
                'insightly.api_key' => $this['config']['insightly']['api_key'],
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

        $this->register(new DoctrineOrmServiceProvider(), [
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
        ]);

        $this->register(new MessageBusProvider());

        // Integration types
        $this->register(new IntegrationTypeStorageServiceProvider(__DIR__ . '/../integration_types.yml'));

        // Project
        $this->register(new ProjectProvider());

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
