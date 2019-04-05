<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\EventListener;

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
     * @param ProjectEvent $projectCreated
     * @throws \Exception
     */
    public function handle(ArticleLinkEvent $articleLinkCreated)
    {
        $url = $articleLinkCreated->getUrl();
        $cdbid = $articleLinkCreated->getCdbid();

        /** @var CacheInterface $cacheEntity */
        $cacheEntity = $this->cacheRepository->find($url);

        if (empty($cacheEntity)) {
            // no cache
            $this->articleLinkerClient->linkArticle($url, $cdbid);
            $cache = new Cache();
            $cache->setUrl($url);
            $cache->setLastChecked();
            $this->entityManager->persist($cache);
            $this->entityManager->flush();
        } else {
            $oneHourAgo = new \DateTime();
            $oneHourAgo->modify('-1 hour');
            $lastChecked = $cacheEntity->getLastChecked();

            // one hour cache
            if ($lastChecked < $oneHourAgo) {
                $this->articleLinkerClient->linkArticle($url, $cdbid);
                $cacheEntity->setLastChecked();
                $this->entityManager->merge($cacheEntity);
                $this->entityManager->flush();
            }
        }
    }
}
