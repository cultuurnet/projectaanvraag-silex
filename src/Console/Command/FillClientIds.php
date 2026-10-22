<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Console\Command;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;

final class FillClientIds extends Command
{
    private const REQUIRED_COLUMNS = [
        'live_search_api3_key',
        'test_search_api3_key',
        'live_client_id',
        'test_client_id',
    ];

    protected function configure(): void
    {
        $this->setName('projectaanvraag:fill-client-ids')
            ->setDescription(
                'Fill in the live/test client ids of projects based on their live/test search api 3 keys, '
                . 'only when both client ids are still empty.'
            )
            ->addArgument(
                'input',
                InputArgument::REQUIRED,
                'The full path of the CSV file with columns: '
                . implode(', ', self::REQUIRED_COLUMNS)
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $csvFilePath = $input->getArgument('input');

        if (!is_readable($csvFilePath)) {
            $output->writeln('<error>Could not read CSV file at ' . $csvFilePath . '</error>');
            return 1;
        }

        $csvFile = fopen($csvFilePath, 'rb');
        if ($csvFile === false) {
            $output->writeln('<error>Could not open CSV file at ' . $csvFilePath . '</error>');
            return 1;
        }

        $header = fgetcsv($csvFile);
        if ($header === false) {
            $output->writeln('<error>The CSV file ' . $csvFilePath . ' is empty.</error>');
            fclose($csvFile);
            return 1;
        }

        $columns = array_flip(array_map('trim', $header));
        foreach (self::REQUIRED_COLUMNS as $requiredColumn) {
            if (!isset($columns[$requiredColumn])) {
                $output->writeln('<error>Missing required column "' . $requiredColumn . '" in the CSV file.</error>');
                fclose($csvFile);
                return 1;
            }
        }

        $rowCount = count(file($csvFilePath)) - 1;
        if ($rowCount <= 0) {
            $output->writeln('No data rows found inside ' . $csvFilePath . ' to process.');
            fclose($csvFile);
            return 1;
        }

        $questionHelper = $this->getHelper('question');
        $confirmQuestion = new ConfirmationQuestion(
            'Are you sure you want to process ' . $rowCount . ' rows? (y,N) ',
            false
        );
        if (!$questionHelper->ask($input, $output, $confirmQuestion)) {
            fclose($csvFile);
            return 0;
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getInstance('orm.em');
        $repository = $entityManager->getRepository('ProjectAanvraag:Project');

        $updated = 0;
        $lineNumber = 1;
        while (($row = fgetcsv($csvFile)) !== false) {
            $lineNumber++;

            if ($row === [null] || $row === []) {
                // Skip empty lines.
                continue;
            }

            $liveSearchApiKey = trim((string) ($row[$columns['live_search_api3_key']] ?? ''));
            $testSearchApiKey = trim((string) ($row[$columns['test_search_api3_key']] ?? ''));
            $liveClientId = trim((string) ($row[$columns['live_client_id']] ?? ''));
            $testClientId = trim((string) ($row[$columns['test_client_id']] ?? ''));

            if ($liveSearchApiKey === '' || $testSearchApiKey === '') {
                $output->writeln('Skipped line ' . $lineNumber . ' (missing live/test search api 3 key).');
                continue;
            }

            if ($liveClientId === '' || $testClientId === '') {
                $output->writeln('Skipped line ' . $lineNumber . ' (missing live/test client id).');
                continue;
            }

            /** @var ProjectInterface|null $project */
            $project = $repository->findOneBy(
                [
                    'liveApiKeySapi3' => $liveSearchApiKey,
                    'testApiKeySapi3' => $testSearchApiKey,
                ]
            );
            if ($project === null) {
                $output->writeln(
                    'Skipped line ' . $lineNumber . ' (no project found matching the given search api 3 keys).'
                );
                continue;
            }

            if ($project->getLiveClientId() !== null || $project->getTestClientId() !== null) {
                $output->writeln(
                    'Skipped project ' . $project->getId()
                    . ' (line ' . $lineNumber . '): client id(s) already set.'
                );
                continue;
            }

            $project->setLiveClientId($liveClientId);
            $project->setTestClientId($testClientId);
            $entityManager->flush();
            $updated++;

            $output->writeln(
                'Updated project ' . $project->getId()
                . ' with live client id ' . $liveClientId
                . ' and test client id ' . $testClientId . '.'
            );
        }

        fclose($csvFile);

        $output->writeln('Done. Updated ' . $updated . ' project(s).');

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
