<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

class Organisation extends PrimaryEntityBase implements JsonUnserializeInterface
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

    public function __construct()
    {
        parent::__construct();
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
     * Unserialize json to an Organisation
     * @param string $json
     * @return Organisation
     */
    public static function jsonUnSerialize($json)
    {
        $organisation = new self();
        $data = json_decode($json);

        $organisation->setId(!empty($data->id) ? $data->id : null);
        $organisation->setName(!empty($data->name) ? $data->name : null);
        $organisation->setBackground(!empty($data->background) ? $data->background : null);
        $organisation->setCanDelete(!empty($data->canDelete) ? $data->canDelete : null);

        $organisation->setCanEdit(!empty($data->canEdit) ? $data->canEdit : null);
        $organisation->setDateCreatedUTC(!empty($data->dateCreatedUTC) ? $data->dateCreatedUTC : null);
        $organisation->setDateUpdatedUTC(!empty($data->dateUpdatedUTC) ? $data->dateUpdatedUTC : null);
        $organisation->setImageUrl(!empty($data->imageUrl) ? $data->imageUrl : null);
        $organisation->setOwnerUserId(!empty($data->ownerUserId) ? $data->ownerUserId : null);
        $organisation->setVisibleTeamId(!empty($data->visibleTeamId) ? $data->visibleTeamId : null);

        $organisation->setVisibleTo(!empty($data->visibleTo) ? $data->visibleTo : null);
        $organisation->setVisibleUserIds(!empty($data->visibleUserIds) ? $data->visibleUserIds : null);

        // Addresses
        if (!empty($data->addresses)) {
            foreach ($data->addresses as $item) {
                $organisation->addresses->append(Address::jsonUnSerialize($item));
            }
        }

        // Links
        if (!empty($data->links)) {
            foreach ($data->links as $item) {
                $organisation->links->append(Link::jsonUnSerialize($item));
            }
        }

        // Custom fields
        if (!empty($data->customFields)) {
            foreach ($data->customFields as $key => $value) {
                $organisation->addCustomField($key, $value);
            }
        }

        return $organisation;
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

        $data = parent::toInsightly();

        echo print_r($this, true);

        $data += [
            'ORGANISATION_ID' => $this->getId(),
            'ORGANISATION_NAME' => $this->getName(),
            'BACKGROUND' => $this->getBackground(),
            'ADDRESSES' => $addresses
        ];

        unset($data['VISIBLE_TO']);
        unset($data['CAN_EDIT']);
        unset($data['CAN_DELETE']);
        unset($data['DATE_CREATED_UTC']);
        unset($data['DATE_UPDATED_UTC']);

        echo "boe";

        return array_filter($data);
    }
}
