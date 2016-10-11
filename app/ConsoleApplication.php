<?php

namespace CultuurNet\ProjectAanvraag;

use CultuurNet\ProjectAanvraag\Console\Command\ConsumeCommand;
use CultuurNet\ProjectAanvraag\Project\ProjectControllerProvider;
use CultuurNet\UiTIDProvider\Auth\AuthServiceProvider;
use CultuurNet\UiTIDProvider\CultureFeed\CultureFeedServiceProvider;
use CultuurNet\UiTIDProvider\User\UserServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;
use Knp\Console\ConsoleEvent;
use Knp\Console\ConsoleEvents;
use Knp\Provider\ConsoleServiceProvider;
use Silex\Application as SilexApplication;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use SimpleBus\Asynchronous\Consumer\StandardSerializedEnvelopeConsumer;
use SimpleBus\RabbitMQBundleBridge\RabbitMQMessageConsumer;
use SimpleBus\Serialization\Envelope\DefaultEnvelopeFactory;
use SimpleBus\Serialization\Envelope\Serializer\StandardMessageInEnvelopeSerializer;
use SimpleBus\Serialization\NativeObjectSerializer;

/**
 * Application class for the projectaanvraag app: console version.
 */
class ConsoleApplication extends ApplicationBase
{

    public function __construct()
    {
        parent::__construct();
        $this->registerCommands();
    }

    /**
     * Register all service providers.
     */
    function registerProviders()
    {
        parent::registerProviders();

        $this->register(new ConsoleServiceProvider(), [
           'console.name' => 'Projectaanvraag',
            'console.version' => '1.0.0',
            'console.project_directory' => __DIR__ . '/..',
        ]);
    }

    /**
     * Register all commands.
     */
    function registerCommands()
    {
        $consoleApp = $this['console'];
        $consoleApp->add(new ConsumeCommand('projectaanvraag:consumer', 'rabbit.connection', 'rabbit.consumer'));
    }

}