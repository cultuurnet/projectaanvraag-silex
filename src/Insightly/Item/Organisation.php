<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

class Organisation extends PrimaryEntityBase
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $background;

    /**
     * @var Address[]
     */
    protected $addresses;

    /**
     * @var ContactInfo[]
     */
    protected $contactInfo;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Organisation
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * @param string $background
     * @return Organisation
     */
    public function setBackground($background)
    {
        $this->background = $background;
        return $this;
    }

    /**
     * @return \CultuurNet\ProjectAanvraag\Address[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param \CultuurNet\ProjectAanvraag\Address[] $addresses
     * @return Organisation
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;
        return $this;
    }

    /**
     * Add an address.
     * @param Address $address
     */
    public function addAddress(Address $address)
    {
        $this->addresses[] = $address;
    }

    /**
     * @return ContactInfo[]
     */
    public function getContactInfo()
    {
        return $this->contactInfo;
    }

    /**
     * @param ContactInfo[] $contactInfo
     * @return Organisation
     */
    public function setContactInfo($contactInfo)
    {
        $this->contactInfo = $contactInfo;
        return $this;
    }
}