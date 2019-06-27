<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\EventListener;

use CultuurNet\ProjectAanvraag\ArticleLinker\ArticleLinkerClientInterface;
use CultuurNet\ProjectAanvraag\ArticleLinker\Event\ArticleLinkCreated;
use CultuurNet\ProjectAanvraag\Entity\Cache;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectActivated;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectBlocked;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ArticleLinkCreatedEventListenerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ArticleLinkerClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $articleLinkerClient;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    /**
     * @var ArticleLinkCreatedEventListener
     */
    protected $eventListener;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {

        $this->articleLinkerClient = $this
            ->getMockBuilder(ArticleLinkerClientInterface::class)
            ->getMock();
        $this->entityManager = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->getMock();
        $this->repository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventListener = new ArticleLinkCreatedEventListener(
            $this->articleLinkerClient,
            $this->entityManager,
            $this->repository
        );
    }

    /**
     * Test the event listener handler with an existing / expired cache entity.
     */
    public function testExistingHandle()
    {
        $lastChecked = new \DateTime();
        $lastChecked->modify('-61 minutes');

        $cacheEntity = $this
            ->getMockBuilder(Cache::class)
            ->getMock();
        $cacheEntity->expects($this->once())
            ->method('getLastChecked')
            ->willReturn($lastChecked);

        $this->repository->expects($this->once())
            ->method('find')
            ->with('my-url')
            ->willReturn($cacheEntity);

        $this->articleLinkerClient->expects($this->once())
            ->method('linkArticle')
            ->with('my-url', 'my-cdbid');
        $cacheEntity->expects($this->once())
            ->method('setLastChecked');
        $this->entityManager->expects($this->once())
            ->method('merge')
            ->with($cacheEntity);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $articleLinkCreated = new ArticleLinkCreated('my-url', 'my-cdbid');
        $this->eventListener->handle($articleLinkCreated);
    }

    /**
     * Test the handler when a cache entity was not found.
     */
    public function testNonExistingHandle()
    {
        $this->repository->expects($this->once())
            ->method('find')
            ->with('my-url')
            ->willReturn(null);

        $this->articleLinkerClient->expects($this->once())
            ->method('linkArticle')
            ->with('my-url', 'my-cdbid');
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $articleLinkCreated = new ArticleLinkCreated('my-url', 'my-cdbid');
        $this->eventListener->handle($articleLinkCreated);
    }

    /**
     * Test the event listener handler with an existing valid cache entry.
     */
    public function testHandleNoLinkNeeded()
    {
        $lastChecked = new \DateTime();
        $lastChecked->modify('-59 minutes');

        $cacheEntity = $this
            ->getMockBuilder(Cache::class)
            ->getMock();
        $cacheEntity->expects($this->once())
            ->method('getLastChecked')
            ->willReturn($lastChecked);

        $this->repository->expects($this->once())
            ->method('find')
            ->with('my-url')
            ->willReturn($cacheEntity);

        $this->articleLinkerClient->expects($this->never())
            ->method('linkArticle');

        $articleLinkCreated = new ArticleLinkCreated('my-url', 'my-cdbid');
        $this->eventListener->handle($articleLinkCreated);
    }
}
