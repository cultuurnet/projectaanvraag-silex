<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\Event;

use JMS\Serializer\Annotation\Type;

class ArticleLinkCreated extends ArticleLinkEvent
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
     * ProjectCreated constructor.
     * @param string $url
     * @param string $cdbid
     */
    public function __construct($url, $cdbid)
    {
        parent::__construct($url, $cdbid);
        $this->url = $url;
        $this->cdbid = $cdbid;
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
     * @return ArticleLinkCreated
     */
    public function setCbid($cdbid)
    {
        $this->cdbid = $cdbid;
        return $this;
    }
}
