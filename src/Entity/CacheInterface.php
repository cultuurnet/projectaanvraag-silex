<?php

namespace CultuurNet\ProjectAanvraag\Entity;

interface CacheInterface extends EntityInterface
{
    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $url
     * @return CacheInterface
     */
    public function setUrl($url);

    /**
     * @return string
     */
    public function getLastChecked();

    /**
     * @param DateTime $lastChecked
     * @return CacheInterface
     */
    public function setLastChecked($lastChecked);
}
