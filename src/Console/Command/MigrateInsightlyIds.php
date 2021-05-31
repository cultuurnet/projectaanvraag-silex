<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Console\Command;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;

final class MigrateInsightlyIds extends Command
{
    protected function configure(): void
    {
        $this->setName('projectaanvraag:migrate-insightly-ids')
            ->setDescription('Migrate the Ids of the new Insightly instance based on a CSV file')
            ->addArgument('input', InputArgument::REQUIRED, 'The full path of the CSV file with Ids to migrate');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $csvFilePath = $input->getArgument('input');

        $rowCount = count(file($csvFilePath));
        if ($rowCount <= 0) {
            $output->writeln('No lines (projects) found inside ' . $csvFilePath . ' to migrate');
            return 1;
        }

        $questionHelper = $this->getHelper('question');
        $confirmQuestion = new ConfirmationQuestion('Are you sure you want to migrate ' . $rowCount . ' projects? (y,N) ', false);
        if (!$questionHelper->ask($input, $output, $confirmQuestion)) {
            return 0;
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getInstance('orm.em');
        $csvFile = fopen($csvFilePath, 'rb');
        while (($row = fgetcsv($csvFile)) !== false) {
            if (count($row) !== 3) {
                $output->writeln(
                    'Skipped project with legacy id ' . $row[0] . ' (' . count($row) . ' columns instead of 3)'
                );
                continue;
            }

            [$legacyId, $opportunityId, $projectId] = $row;

            /** @var ProjectInterface $project */
            $project = $entityManager->getRepository('ProjectAanvraag:Project')->findOneBy(
                [
                    'insightlyProjectId' => $legacyId,
                ]
            );
            if ($project === null) {
                $output->writeln('Skipped project with legacy id ' . $legacyId . ' (not found inside database)');
                continue;
            }

            if (!empty($opportunityId)) {
                $project->setOpportunityIdInsightly((int) $opportunityId);
            }
            if (!empty($projectId)) {
                $project->setProjectIdInsightly((int) $projectId);
            }

            $entityManager->flush();
            $output->writeln('Updated project with legacy id ' . $legacyId);
        }

        return 0;
    }

    private function getInstance(string $serviceId)
    {
        $app = $this->getSilexApplication();

        if (!isset($app[$serviceId])) {
            throw new ProviderNotFoundException($serviceId . ' not found');
        }

        return $app[$serviceId];
    }
}
