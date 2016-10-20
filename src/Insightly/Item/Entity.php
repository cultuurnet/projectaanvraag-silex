<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

/**
 * Base class for Insightly entities.
 */
class Entity implements EntityInterface, \JsonSerializable
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Entity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Serialize the entity to json
     * @return array
     */
    public function jsonSerialize()
    {
        return ['id' => $this->id];
    }
}
