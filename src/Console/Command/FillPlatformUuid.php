<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Console\Command;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;

final class FillPlatformUuid extends Command
{
    protected function configure()
    {
        $this->setName('projectaanvraag:fill-platform-uuid')
            ->setAliases(['fpu']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Fixing platform uuids');

        $insightlyRows = $this->readCsvFile('insightly_mappings.csv');

        $insightlyMapping = [];
        foreach ($insightlyRows as $insightlyRow) {
            if (isset($insightlyMapping[(int) $insightlyRow[1]])) {
                $output->writeln('Duplicate Insightly id found: ' . $insightlyRow[1]);
                continue;
            }

            $insightlyMapping[(int) $insightlyRow[1]] = $insightlyRow[0];
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getInstance('orm.em');
        $projects = $entityManager->getRepository('ProjectAanvraag:Project')->findAll();

        /** @var ProjectInterface $project */
        foreach ($projects as $project) {
            $platformUuid = null;

            if ($project->getOpportunityIdInsightly() && isset($insightlyMapping[$project->getOpportunityIdInsightly()])) {
                $platformUuid = $insightlyMapping[$project->getOpportunityIdInsightly()];
            }

            if ($project->getProjectIdInsightly() && isset($insightlyMapping[$project->getProjectIdInsightly()])) {
                $platformUuid = $insightlyMapping[$project->getProjectIdInsightly()];
            }

            if ($platformUuid === null) {
                $output->writeln('No platform uuid found for project ' . $project->getId());
                continue;
            }

            if ($project->getPlatformUuid() !== null && $project->getPlatformUuid() === $platformUuid) {
                $output->writeln('Project already has the correct platform Uuid ' . $project->getId());
                continue;
            }

            if ($project->getPlatformUuid() !== null && $project->getPlatformUuid() !== $platformUuid) {
                $output->writeln('Project has a different platform Uuid ' . $project->getId());
                continue;
            }

            $output->writeln('Fixing project ' . $project->getId() . ' with platform uuid ' . $platformUuid);
            $entityManager->persist($project);
        }
    }

    private function readCsvFile(string $csvFile): array
    {
        $rows = [];
        /** @var resource $fileHandle */
        $fileHandle = fopen($csvFile, 'rb');

        while (!feof($fileHandle)) {
            $row = fgetcsv($fileHandle);
            if ($row === false) {
                break;
            }

            $rows[] = $row;
        }
        fclose($fileHandle);

        return $rows;
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