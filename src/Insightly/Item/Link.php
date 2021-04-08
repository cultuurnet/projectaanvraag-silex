<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use CultuurNet\ProjectAanvraag\Insightly\InsightlySerializableInterface;
use CultuurNet\ProjectAanvraag\Insightly\Parser\LinkParser;

class Link extends Entity implements InsightlySerializableInterface, JsonUnserializeInterface
{
    /**
     * @var int
     */
    protected $contactId;

    /**
     * @var int
     */
    protected $opportunityId;

    /**
     * @var int
     */
    protected $organisationId;

    /**
     * @var int
     */
    protected $projectId;

    /**
     * @var int
     */
    protected $secondProjectId;

    /**
     * @var int
     */
    protected $secondOpportunityId;

    /**
     * @var string
     */
    protected $role;

    /**
     * @var string
     */
    protected $details;

    /**
     * @return int
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * @param int $contactId
     * @return Link
     */
    public function setContactId($contactId)
    {
        $this->contactId = $contactId;
        return $this;
    }

    /**
     * @return int
     */
    public function getOpportunityId()
    {
        return $this->opportunityId;
    }

    /**
     * @param int $opportunityId
     * @return Link
     */
    public function setOpportunityId($opportunityId)
    {
        $this->opportunityId = $opportunityId;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrganisationId()
    {
        return $this->organisationId;
    }

    /**
     * @param int $organisationId
     * @return Link
     */
    public function setOrganisationId($organisationId)
    {
        $this->organisationId = $organisationId;
        return $this;
    }

    /**
     * @return int
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * @param int $projectId
     * @return Link
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
        return $this;
    }

    /**
     * @return int
     */
    public function getSecondProjectId()
    {
        return $this->secondProjectId;
    }

    /**
     * @param int $secondProjectId
     * @return Link
     */
    public function setSecondProjectId($secondProjectId)
    {
        $this->secondProjectId = $secondProjectId;
        return $this;
    }

    /**
     * @return int
     */
    public function getSecondOpportunityId()
    {
        return $this->secondOpportunityId;
    }

    /**
     * @param int $secondOpportunityId
     * @return Link
     */
    public function setSecondOpportunityId($secondOpportunityId)
    {
        $this->secondOpportunityId = $secondOpportunityId;
        return $this;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $role
     * @return Link
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param mixed $details
     * @return Link
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function jsonUnSerialize($json)
    {
        $link = new self();

        $link->setId(!empty($json->id) ? $json->id : null);
        $link->setContactId(!empty($json->contactId) ? $json->contactId : null);
        $link->setDetails(!empty($json->details) ? $json->details : null);
        $link->setOpportunityId(!empty($json->opportunityId) ? $json->opportunityId : null);
        $link->setOrganisationId(!empty($json->organisationId) ? $json->organisationId : null);
        $link->setProjectId(!empty($json->projectId) ? $json->projectId : null);
        $link->setRole(!empty($json->role) ? $json->role : null);
        $link->setSecondOpportunityId(!empty($json->secondOpportunityId) ? $json->secondOpportunityId : null);
        $link->setSecondProjectId(!empty($json->secondProjectId) ? $json->secondProjectId : null);

        return $link;
    }

    /**
     * {@einheritdoc}
     */
    public function toInsightly()
    {
        $data = [
            'LINK_ID' => $this->getId(),
            'ROLE' => $this->getRole(),
            'DETAILS' => $this->getDetails(),
        ];

        if ($this->getContactId()) {
            $data['LINK_OBJECT_ID'] = $this->getContactId();
            $data['LINK_OBJECT_NAME'] = LinkParser::OBJECT_NAME_CONTACT;
        }

        if ($this->getOrganisationId()) {
            $data['LINK_OBJECT_ID'] = $this->getOrganisationId();
            $data['LINK_OBJECT_NAME'] = LinkParser::OBJECT_NAME_ORGANIZATION;
        }

        return array_filter($data);
    }
}
