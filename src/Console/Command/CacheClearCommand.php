<?php

namespace CultuurNet\ProjectAanvraag\Console\Command;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Knp\Command\Command;
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
        $this->setName('projectaanvraag:cache-clear');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /** @var FilesystemCache $filesystemCache */
        $filesystemCache = $this->getInstance('cache_doctrine_filesystem');
        $filesystemCache->flushAll();

        /** @var RedisCache $redisCache */
        $redisCache = $this->getInstance('cache_doctrine_redis');
        $redisCache->flushAll();
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
