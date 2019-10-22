<?php

namespace CultuurNet\ProjectAanvraag\Widget;

/**
 * Provides an interface for widget pages.
 */
interface WidgetPageInterface
{

    /**
     * Current version of widgets.
     */
    const CURRENT_VERSION = 3;

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
     * Get the version number of this widget.
     *
     * @return int
     */
    public function getVersion();

    /**
     * Set the version number of this widget.
     *
     * @param int $version
     */
    public function setVersion($version);

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
     * Mark WidgetPage as Published
     */
    public function publish();

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
     * Get the user who last updated this.
     *
     * @return string
     */
    public function getLastUpdated();

    /**
     * Set the last updated date as timestamp.
     *
     * @param int $lastUpdated
     */
    public function setLastUpdated($lastUpdated);

    /**
     * Get the user who last updated this.
     *
     * @return string
     */
    public function getLastUpdatedBy();

    /**
     * Set the user who last updated this.
     *
     * @param string $userID
     */
    public function setLastUpdatedBy($userID);

    /**
     * Get the user who created this.
     *
     * @return string
     */
    public function getCreatedBy();

    /**
     * Set the user who created this WidgetPage.
     *
     * @param string $userID
     */
    public function setCreatedBy($userID);

    /**
     * Get the creation date.
     *
     * @return string
     */
    public function getCreated();

    /**
     * Set the creation date as timestamp.
     *
     * @param int $created
     */
    public function setCreated($created);

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

    public function setSelectedTheme(?string $selectedTheme);

    public function getSelectedTheme() : ?string;

    /**
     * Get the wanted viewportmode of a page
     *
     * @return boolean
     */
    public function getMobile();

    /**
     * Set the wanted viewportmode of a page
     *
     * @param boolean $mobile
     */
    public function setMobile($mobile);

    /**
     * Does the page does not want jQuery?
     *
     * @return boolean
     */
    public function getJquery();

    /**
     * Set the desire of the page to exclude jQuery
     *
     * @param boolean $preventJQuery
     */
    public function setJquery($preventJQuery);

    /**
     * Get the widget with the given id.
     *
     * @param $widgetId
     * @return WidgetTypeInterface|null
     */
    public function getWidget($widgetId);
}
