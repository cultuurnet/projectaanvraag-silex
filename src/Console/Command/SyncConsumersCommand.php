<?php

namespace CultuurNet\ProjectAanvraag\Console\Command;

use CultuurNet\ProjectAanvraag\Core\Event\QueueConsumers;
use Knp\Command\Command;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;

/**
 * Starts a one-way synchronisation of the test and live culturefeed consumers
 */
class SyncConsumersCommand extends Command
{
    /**
     * Configure the command
     */
    public function configure()
    {
        $this->setName('sync-consumers')
            ->setDescription('Sync the culturefeed consumers to the local database.')
            ->addArgument('env', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Which environments do you want to sync (eg. test, live)?');
    }

    /**
     * Execute the command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = $input->getArgument('env');

        /** @var MessageBusSupportingMiddleware $eventBus */
        $eventBus = $this->getInstance('event_bus');

        // test
        if (in_array(QueueConsumers::CONSUMER_TYPE_TEST, $env)) {
            $eventBus->handle(new QueueConsumers(QueueConsumers::CONSUMER_TYPE_TEST));
        }

        // live
        if (in_array(QueueConsumers::CONSUMER_TYPE_LIVE, $env)) {
            $eventBus->handle(new QueueConsumers(QueueConsumers::CONSUMER_TYPE_LIVE));
        }

        $output->writeln('Started consumer synchronisation for "'.implode(', ', $env).'".');
    }

    /**
     * Get a service instance.
     */
    private function getInstance($serviceId)
    {
        $app = $this->getSilexApplication();

        if (!isset($app[$serviceId])) {
            throw new ProviderNotFoundException($serviceId . ' not found');
        }

        return $app[$serviceId];
    }
}
