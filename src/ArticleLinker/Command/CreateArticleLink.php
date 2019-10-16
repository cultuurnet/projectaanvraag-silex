<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\Command;

class CreateArticleLink
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $cdbid;

    /**
     * @var string
     */
    private $projectActive;


    /**
     * CreateArticleLink constructor.
     * @param $url
     * @param $cdbid
     */
    public function __construct($url, $cdbid)
    {
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
     * @param string $url
     * @return CreateArticleLink
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
     * @return Boolean
     */
    public function getProjectActive()
    {
        return $this->projectActive;
    }

    /**
     * @param string $cdbid
     * @return CreateArticleLink
     */
    public function setCdbid($cdbid)
    {
        $this->cdbid = $cdbid;
        return $this;
    }
}
