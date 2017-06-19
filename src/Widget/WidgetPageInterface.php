<?php

namespace CultuurNet\ProjectAanvraag\Widget;

/**
 * Provides an interface for widget pages.
 */
interface WidgetPageInterface
{

    /**
     * Get the id of the page.
     *
     * @return string
     */
    public function getId();

    /**
     * Set the title of the page.
     *
     * @param string $title
     */
    public function setTitle($title);

    /**
     * Get the title of the page
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set the rows of the page.
     *
     * @param LayoutInterface[] $rows
     */
    public function setRows($rows);

    /**
     * Get the rows of the page
     *
     * @return LayoutInterface[]
     */
    public function getRows();
}
