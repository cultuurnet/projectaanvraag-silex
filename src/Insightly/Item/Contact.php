<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

class Contact extends Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var ContactInfo[]
     */
    protected $contactInfos;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Contact
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return Contact
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return Contact
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return ContactInfo[]
     */
    public function getContactInfos()
    {
        return $this->contactInfos;
    }

    /**
     * @param ContactInfo[] $contactInfos
     * @return Contact
     */
    public function setContactInfos($contactInfos)
    {
        $this->contactInfos = $contactInfos;
        return $this;
    }

    /**
     * @param null $type
     * @param null $subType
     * @param null $label
     * @param null $detail
     * @return Contact
     */
    public function addContactInfo($type = null, $subType = null, $label = null, $detail = null)
    {
        $this->contactInfos[] = new ContactInfo($type, $subType, $label, $detail);
        return $this;
    }

    /**
     * Serializes a Contact to an Insightly accepted array
     * @return array
     */
    public function toInsightly()
    {
        $contactInfos = [];
        foreach ($this->getContactInfos() as $contactInfo) {
            $contactInfos[] = $contactInfo->toInsightly();
        }

        $data = [
            'FIRST_NAME' => $this->getFirstName(),
            'LAST_NAME' => $this->getLastName(),
            'CONTACTINFOS' => $contactInfos,
        ];

        if ($this->getId()) {
            $data['CONTACT_ID'] = $this->getId();
        }

        return $data;
    }
}
