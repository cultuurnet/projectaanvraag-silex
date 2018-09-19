<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

class Tag extends Entity
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return ['name' => $this->id];
    }

    /**
     * {@einheritdoc}
     */
    public function toInsightly()
    {
        $data = [
            'TAG_NAME' => $this->getId(),
        ];

        return array_filter($data);
    }
}
