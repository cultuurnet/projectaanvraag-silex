<?php

namespace CultuurNet\ProjectAanvraag\Widget\Command;

/**
 * Provides a command to create a given page.
 */
class MigrateWidgetPage
{
    /**
     * @var array
     */
    protected $result;

    /**
     * MigrateWidgetPage constructor.
     *
     * @param array $result
     */
    public function __construct($result)
    {
        $this->result = $result;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array $result
     * @return MigrateWidgetPage
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }
}
