<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

class Link extends Entity
{
    const LINK_TYPE_CONTACT = 'CONTACT_ID';
    const LINK_TYPE_OPPORTUNITY = 'OPPORTUNITY_ID';
    const LINK_TYPE_ORGANISATION = 'ORGANISATION_ID';
    const LINK_TYPE_PROJECT = 'PROJECT_ID';
    const LINK_TYPE_SECOND_PROJECT = 'SECOND_PROJECT_ID';
    const LINK_TYPE_SECOND_OPPORTUNITY = 'SECOND_OPPORTUNITY_ID';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $linkedId;

    /**
     * @var string
     */
    protected $role;

    /**
     * @var string
     */
    protected $details;

    /**
     * Link constructor.
     * @param string $type
     * @param int $linkedId
     * @param string|null $role
     * @param string|null $details
     */
    public function __construct($type, $linkedId, $role = null, $details = null)
    {
        $this->type = $type;
        $this->linkedId = $linkedId;
        $this->role = $role;
        $this->details = $details;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Link
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Link
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getLinkedId()
    {
        return $this->linkedId;
    }

    /**
     * @param int $linkedId
     * @return Link
     */
    public function setLinkedId($linkedId)
    {
        $this->linkedId = $linkedId;
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
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param string $details
     * @return Link
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * Serializes a Link to an Insightly accepted array
     * @return array
     */
    public function toInsightly()
    {
        return array_filter(
            [
                $this->getType() => $this->getLinkedId(),
                'ROLE' => $this->getRole(),
                'DETAILS' => $this->getDetails(),
            ]
        );
    }
}
