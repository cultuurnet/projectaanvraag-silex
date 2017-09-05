<?php

namespace CultuurNet\ProjectAanvraag\Console\Command;

use CultuurNet\ProjectAanvraag\ApplicationBase;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;

use CultuurNet\ProjectAanvraag\Core\Event\QueueWidgetMigration;

/**
 * Migrates tbe old JSON widgets to the new JSON widgets.
 */
class MigrateCommand extends Command
{
    public function configure()
    {
        $this->setName('projectaanvraag:migrate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Batch migration in pieces of 100 pages
        // Every 100 pages, throw a new migrate command
        // Migrate listener does the real migration

        $eventBus = $this->getInstance('event_bus');

        $eventBus->handle(new QueueWidgetMigration());

        $output->writeln('Starting the migration.');
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
