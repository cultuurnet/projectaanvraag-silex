<?php

namespace CultuurNet\ProjectAanvraag\Console\Command;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use FilesystemIterator;
use Knp\Command\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;

/**
 * Provides a command to clear cache.
 * @codeCoverageIgnore
 */
class CacheClearCommand extends Command
{

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('projectaanvraag:cache-clear')
            ->setAliases(['cc']);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Empty cache folder.
        $this->emptyCacheDirectory();

        /** @var RedisCache $redisCache */
        $redisCache = $this->getInstance('cache_doctrine_redis');
        $redisCache->flushAll();

        $output->writeln('Redis and filesystem cache was cleared');
    }

    /**
     * Remove the given directory.
     */
    private function emptyCacheDirectory() {
        $directoryIterator = new RecursiveDirectoryIterator($this->getInstance('cache_directory'), FilesystemIterator::SKIP_DOTS);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::CHILD_FIRST);
        /** @var \DirectoryIterator $file */
        foreach ($recursiveIterator as $file ) {
            if (!$file->isDir()) {
                unlink($file);
            }
        }
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
