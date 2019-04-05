<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\Event;

use CultuurNet\ProjectAanvraag\Core\AbstractRetryableMessage;
use JMS\Serializer\Annotation\Type;

abstract class ArticleLinkEvent extends AbstractRetryableMessage
{
    /**
     * @var string
     * @Type("string")
     */
    private $cdbid;

    /**
     * @var string
     * @Type("string")
     */
    private $url;

    /**
     * ProjectDeleted constructor.
     * @param string $url
     * @param string $cdbid
     * @param int $delay
     */
    public function __construct($url, $cdbid, $delay = 0)
    {
        $this->url = $url;
        $this->cdbid = $cdbid;
        $this->delay = 5;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param String $string
     * @return ArticleLinkCreated
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getCdbid()
    {
        return $this->cdbid;
    }

    /**
     * @param string $usedCoupon
     * @return ArticleLinkEvent
     */
    public function setCbid($cdbid)
    {
        $this->cdbid = $cdbid;
        return $this;
    }
}
