<?php

namespace CultuurNet\ProjectAanvraag\Console\Command;

use CultuurNet\ProjectAanvraag\Core\Schema\DatabaseSchemaInstaller;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;

/**
 * Installs the application.
 */
class InstallCommand extends Command
{
    public function configure()
    {
        $this->setName('install')
            ->addOption('reinstall', null, InputOption::VALUE_NONE, 'Completely reinstall instead of installing only new tables');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemaInstaller = $this->getDatabaseSchemaInstaller();
        if ($input->getOption('reinstall')) {
            $helper = $this->getHelper('question');
            $confirmation = new ConfirmationQuestion('You are about to remove all existing database tables. Are you sure? (y/n)', false);
            if ($helper->ask($input, $output, $confirmation) == 'y') {
                $schemaInstaller->uninstallSchema();
                $output->writeln('Existing database schema uninstalled.');
            } else {
                $output->writeln('Continuing without dropping tables.');
            }
        }

        $this->getDatabaseSchemaInstaller()->installSchema();
        $output->writeln('Database schema installed.');
    }

    /**
     * Get the database schema installer.
     * @return DatabaseSchemaInstaller
     */
    private function getDatabaseSchemaInstaller()
    {
        $app = $this->getSilexApplication();

        if (!isset($app['database.installer'])) {
            throw new ProviderNotFoundException('database.installer not found');
        }

        return $app['database.installer'];
    }
}
