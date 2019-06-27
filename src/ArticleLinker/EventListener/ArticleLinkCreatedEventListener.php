<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\EventListener;

use CultuurNet\ProjectAanvraag\ArticleLinker\Event\ArticleLinkCreated;
use CultuurNet\ProjectAanvraag\ArticleLinker\Event\ArticleLinkEvent;
use CultuurNet\ProjectAanvraag\ArticleLinkerAPI\ArticleLinkerAPIServiceProvider;
use CultuurNet\ProjectAanvraag\Entity\CacheInterface;
use CultuurNet\ProjectAanvraag\Entity\Cache;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ArticleLinkCreatedEventListener
{
    /**
     * @var ArticleLinkerClientInterface
     */
    protected $articleLinkerClient;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $cacheRepository;

    /**
     * ArticleLinkCreatedEventListener constructor.
     * @param ArticleLinkerClientInterface $articleLinkerClient
     */
    public function __construct($articleLinkerClient, $entityManager, $repository)
    {
        $this->articleLinkerClient = $articleLinkerClient;
        $this->entityManager = $entityManager;
        $this->cacheRepository = $repository;
    }

    /**
     * Handle the command
     * @param ArticleLinkCreated $articleLinkCreated
     * @throws \Exception
     */
    public function handle(ArticleLinkCreated $articleLinkCreated)
    {
        $url = $articleLinkCreated->getUrl();
        $cdbid = $articleLinkCreated->getCdbid();

        /** @var CacheInterface $cacheEntity */
        $cacheEntity = $this->cacheRepository->find($url);
        $now = new \DateTime("now");

        // No cache entry found.
        if (empty($cacheEntity)) {
            $this->articleLinkerClient->linkArticle($url, $cdbid);

            $cache = new Cache();
            $cache->setUrl($url);
            $cache->setLastChecked($now);
            $this->entityManager->persist($cache);
            $this->entityManager->flush();
        } else {
            $oneHourAgo = new \DateTime();
            $oneHourAgo->modify('-1 hour');
            $lastChecked = $cacheEntity->getLastChecked();

            // Check if the entry is expired (1 hour).
            if ($lastChecked < $oneHourAgo) {
                $this->articleLinkerClient->linkArticle($url, $cdbid);
                $cacheEntity->setLastChecked($now);
                $this->entityManager->merge($cacheEntity);
                $this->entityManager->flush();
            }
        }
    }
}
