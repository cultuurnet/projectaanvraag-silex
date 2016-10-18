<?php

namespace CultuurNet\ProjectAanvraag;

use CultuurNet\ProjectAanvraag\Core\CoreProvider;
use CultuurNet\ProjectAanvraag\Core\MessageBusProvider;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyServiceProvider;
use CultuurNet\ProjectAanvraag\Project\ProjectProvider;
use CultuurNet\UiTIDProvider\Auth\AuthServiceProvider;
use CultuurNet\UiTIDProvider\CultureFeed\CultureFeedServiceProvider;
use CultuurNet\UiTIDProvider\User\UserServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;
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
        $this->register(new CoreProvider());
        $this->register(
            new DoctrineServiceProvider(),
            [
                'db.options' => $this['config']['database'],
            ]
        );
        $this->register(new MessageBusProvider());

        // Project
        $this->register(new ProjectProvider());

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
        $this->register(new UserServiceProvider());

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
