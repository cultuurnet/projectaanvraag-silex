<?php

namespace CultuurNet\ProjectAanvraag\Entity;

interface EntityInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return EntityInterface
     */
    public function setId($id);
}
