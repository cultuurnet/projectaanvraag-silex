<?php

namespace CultuurNet\ProjectAanvraag\Entity;

use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationType;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Coupon entity.
 */
class ProjectTest extends TestCase
{

    /**
     * Test if the setters and getters work.
     */
    public function testGetAndSet()
    {
        $project = new Project();
        $project->setId('my-id');
        $this->assertEquals('my-id', $project->getId());

        $project->setName('my-name');
        $this->assertEquals('my-name', $project->getName());

        $project->setTestConsumerKey('my-consumer-key');
        $this->assertEquals('my-consumer-key', $project->getTestConsumerKey());

        $project->setTestConsumerSecret('my-consumer-secret');
        $this->assertEquals('my-consumer-secret', $project->getTestConsumerSecret());

        $project->setLiveConsumerKey('my-live-consumer-key');
        $this->assertEquals('my-live-consumer-key', $project->getLiveConsumerKey());

        $project->setLiveConsumerSecret('my-live-consumer-secret');
        $this->assertEquals('my-live-consumer-secret', $project->getLiveConsumerSecret());

        $project->setLiveApiKeySapi3('my-live-sapi-key');
        $this->assertEquals('my-live-sapi-key', $project->getLiveApiKeySapi3());

        $project->setTestApiKeySapi3('my-test-sapi-key');
        $this->assertEquals('my-test-sapi-key', $project->getTestApiKeySapi3());

        $project->setStatus('my-status');
        $this->assertEquals('my-status', $project->getStatus());

        $project->setUserId('my-user-id');
        $this->assertEquals('my-user-id', $project->getUserId());

        $created = new \DateTime('-30 minutes');
        $project->setCreated($created);
        $this->assertEquals($created, $project->getCreated());

        $updated = new \DateTime('+30 minutes');
        $project->setUpdated($updated);
        $this->assertEquals($updated, $project->getUpdated());

        $project->setDescription('my-description');
        $this->assertEquals('my-description', $project->getDescription());

        $project->setLogo('my-logo');
        $this->assertEquals('my-logo', $project->getLogo());

        $project->setDomain('my-domain');
        $this->assertEquals('my-domain', $project->getDomain());

        $project->setGroupId(5);
        $this->assertEquals(5, $project->getGroupId());

        $group = new IntegrationType();
        $group->setGroupId(2);
        $project->setGroup($group);
        $this->assertEquals($group, $project->getGroup());

        $project->setInsightlyProjectId('my-insightly-id');
        $this->assertEquals('my-insightly-id', $project->getInsightlyProjectId());

        $project->setContentFilter('my-filter');
        $this->assertEquals('my-filter', $project->getContentFilter());

        $project->setCoupon('my-coupon');
        $this->assertEquals('my-coupon', $project->getCoupon());

        $project->setSapiVersion('my-api-version');
        $this->assertEquals('my-api-version', $project->getSapiVersion());

        $project->setTotalWidgets(3);
        $this->assertEquals(3, $project->getTotalWidgets());

        $project->setCoupon('my-coupon');
        $this->assertEquals('my-coupon', $project->getCoupon());
    }

    /**
     * Test the enrich with consuemr info.
     */
    public function testEnrichWithConsumerInfo()
    {
        $consumer = new \CultureFeed_Consumer();
        $consumer->name = 'my-name';
        $consumer->description = 'my-description';
        $consumer->logo = 'my-logo';
        $consumer->domain = 'my-domain';
        $consumer->searchPrefixSapi3 = 'my-sapi3';
        $consumer->searchPrefixFilterQuery = 'my-sapi2';

        $project = new Project();
        $project->enrichWithConsumerInfo($consumer, 3);
        $this->assertEquals('my-name', $project->getName());
        $this->assertEquals('my-description', $project->getDescription());
        $this->assertEquals('my-logo', $project->getLogo());
        $this->assertEquals('my-domain', $project->getDomain());
        $this->assertEquals('my-sapi3', $project->getContentFilter());

        $project = new Project();
        $project->enrichWithConsumerInfo($consumer, 2);
        $this->assertEquals('my-name', $project->getName());
        $this->assertEquals('my-description', $project->getDescription());
        $this->assertEquals('my-logo', $project->getLogo());
        $this->assertEquals('my-domain', $project->getDomain());
        $this->assertEquals('my-sapi2', $project->getContentFilter());
    }
}
