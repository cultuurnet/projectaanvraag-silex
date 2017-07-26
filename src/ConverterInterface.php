<?php

namespace CultuurNet\ProjectAanvraag;

/**
 * Defines an interface for converters.
 */
interface ConverterInterface
{

    /**
     * Convert the given value.
     */
    public function convert($value);

}