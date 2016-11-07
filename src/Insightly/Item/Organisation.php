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
     * @var EntityList
     */
    protected $addresses;

    /**
     * @var EntityList
     */
    protected $contactInfo;

    public function __construct()
    {
        parent::__construct();
        $this->contactInfo = new EntityList();
        $this->addresses = new EntityList();
    }

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
     * @return EntityList
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param EntityList $addresses
     * @return Organisation
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;
        return $this;
    }

    /**
     * @return EntityList
     */
    public function getContactInfo()
    {
        return $this->contactInfo;
    }

    /**
     * @param EntityList
     * @return Organisation
     */
    public function setContactInfo($contactInfo)
    {
        $this->contactInfo = $contactInfo;
        return $this;
    }

    /**
     * Add contact info.
     * @param ContactInfo $contactInfo
     */
    public function addContactInfo($contactInfo)
    {
        $this->contactInfo[] = $contactInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function toInsightly()
    {
        $addresses = [];
        foreach ($this->addresses as $address) {
            $addresses[] = $address->toInsightly();
        }

        $contactInfo = [];
        foreach ($this->contactInfo as $info) {
            $contactInfo[] = $info->toInsightly();
        }

        $data = parent::toInsightly();

        $data += [
            'ORGANISATION_ID' => $this->getId(),
            'ORGANISATION_NAME' => $this->getName(),
            'BACKGROUND' => $this->getBackground(),
            'ADDRESSES' => $addresses,
            'CONTACTINFOS' => $contactInfo,
        ];

        return array_filter($data);
    }
}
