<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

class Tag extends Entity
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->id;
    }
}
