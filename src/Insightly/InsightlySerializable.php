<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

interface InsightlySerializable
{

    /**
     * Format the object to a format for insightly.
     * @return array
     */
    public function toInsightly();

}