<?php

namespace CultuurNet\ProjectAanvraag\Entity;

/**
 * Tests the Cache entity.
 */
class CacheTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test if the setters and getters work.
     */
    public function testGetAndSet()
    {
        $cache = new Cache();
        $cache->setUrl('my-url');
        $this->assertEquals('my-url', $cache->getUrl());

        $dateTime = new \DateTime();
        $cache->setLastChecked($dateTime);
        $this->assertEquals($dateTime, $cache->getLastChecked());
    }
}
