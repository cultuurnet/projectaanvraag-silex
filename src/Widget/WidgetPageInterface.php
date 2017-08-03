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
     * Set the id of the page.
     *
     * @param string $id
     */
    public function setId($id);

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

    /**
     * Check if this is a draft version
     *
     * @return boolean
     */
    public function isDraft();

    /**
     * Set as a draft version
     */
    public function setAsDraft();

    /**
     * Get the projectID
     *
     * @return string
     */
    public function getProjectId();

    /**
     * Set the projectID
     *
     * @param string $projectId
     */
    public function setProjectId($projectId);

    /**
     * Get the user who last updated this
     *
     * @return string
     */
    public function getLastUpdatedByUser();

    /**
     * Set the user who last updated this
     *
     * @param string $userID
     */
    public function setLastUpdatedByUser($userID);

    /**
     * Get the user who created this
     *
     * @return string
     */
    public function getCreatedByUser();

    /**
     * Set the user who created this WidgetPage
     *
     * @param string $userID
     */
    public function setCreatedByUser($userID);

    /**
     * Set the css that needs to be applied to the current page.
     *
     * @param string $css
     */
    public function setCss($css);

    /**
     * Get the css that needs to be applied to the current page.
     *
     * @return string
     */
    public function getCss();

    /**
     * Get the widget with the given id.
     *
     * @param $widgetId
     * @return WidgetTypeInterface|null
     */
    public function getWidget($widgetId);

}
