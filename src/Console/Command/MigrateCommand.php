<?php

namespace CultuurNet\ProjectAanvraag\Console\Command;

use CultuurNet\ProjectAanvraag\ApplicationBase;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $output->writeln('Starting the migragtion.');

        // Batch migration in pieces of 100 pages
        // Every 100 pages, throw a new migrate command
        // Migrate listener does the real migration


//        $app = ApplicationBase::class;
//        $app->get('/blog/{id}', function ($id) use ($app) {
//            $sql = "SELECT * FROM posts WHERE id = ?";
//            $post = $app['db']->fetchAssoc($sql, array((int) $id));
//
//            return  "<h1>{$post['title']}</h1>".
//                "<p>{$post['body']}</p>";
//        });
    }
}
