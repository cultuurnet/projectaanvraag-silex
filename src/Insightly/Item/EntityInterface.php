<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

/**
 * Interface for Insightly entities.
 */
interface EntityInterface
{
    /**
     * Get the id of this entity.
     * @return string
     */
    public function getId();

    /**
     * Set the id of this entity.
     * @param string $id
     *   The id to set.
     */
    public function setId($id);
}
