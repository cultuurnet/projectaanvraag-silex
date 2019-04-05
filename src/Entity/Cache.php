<?php

namespace CultuurNet\ProjectAanvraag\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="cache",
 *     indexes={
 *         @ORM\Index(name="url", columns={"url"}),
 *     }
 * )
 */
class Cache
{

    /**
     * @ORM\Column(name="url", type="string", length=255)
     * @ORM\Id
     * @Type("string")
     * @var string
     */
    protected $url;

    /**
     * @ORM\Column(name="lastChecked", type="datetime")
     * @Type("datetime")
     * @var DateTime
     */
    protected $lastChecked;

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->lastChecked;
    }

    /**
     * @param string $url
     * @return Cache
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastChecked()
    {
        return $this->lastChecked;
    }

    /**
     * @param DateTime $timestamp
     * @return Cache
     */
    public function setLastChecked()
    {
        $this->lastChecked = new \DateTime("now");
        return $this;
    }
}
