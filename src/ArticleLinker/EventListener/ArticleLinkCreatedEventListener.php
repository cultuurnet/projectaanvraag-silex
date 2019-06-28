<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\EventListener;

use CultuurNet\ProjectAanvraag\ArticleLinker\ArticleLinkerClientInterface;
use CultuurNet\ProjectAanvraag\ArticleLinker\Event\ArticleLinkCreated;
use Symfony\Component\Cache\Simple\AbstractCache;

class ArticleLinkCreatedEventListener
{
    /**
     * @var ArticleLinkerClientInterface
     */
    protected $articleLinkerClient;

    /**
     * @var AbstractCache
     */
    protected $cacheBackend;

    /**
     * ArticleLinkCreatedEventListener constructor.
     * @param ArticleLinkerClientInterface $articleLinkerClient
     * @param AbstractCache $cacheBackend
     */
    public function __construct(ArticleLinkerClientInterface $articleLinkerClient, $cacheBackend = null)
    {
        $this->articleLinkerClient = $articleLinkerClient;
        $this->cacheBackend = $cacheBackend;
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

        $cacheId = md5($url . ':' . $cdbid);
        if ($this->cacheBackend) {
            if (!$this->cacheBackend->has($cacheId)) {
                $this->articleLinkerClient->linkArticle($url, $cdbid);

                $this->cacheBackend->set(
                    $cacheId,
                    [
                        'url' => $url,
                        'cdbid' => $cdbid,
                    ]
                );
            }
        } else {
            $this->articleLinkerClient->linkArticle($url, $cdbid);
        }
    }
}
