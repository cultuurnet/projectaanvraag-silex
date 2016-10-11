<?php

namespace CultuurNet\ProjectAanvraag;

/**
 * Provides a class for callable testing.
 */
class CallableMock
{

    private $isCalled = false;

    /**
     * Is the callable called.
     */
    public function isCalled()
    {
        return $this->isCalled;
    }

    public function __invoke()
    {
        $this->isCalled = true;
    }
}
