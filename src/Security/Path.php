<?php

namespace CultuurNet\UiTIDProvider\Security;

class Path
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string[]
     */
    private $methods;

    /**
     * Path constructor.
     * @param string $pattern
     * @param string|string[] $methods
     */
    public function __construct($pattern, $methods)
    {
        $this->pattern = $pattern;
        $this->methods = is_array($methods) ? $methods : [$methods];
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return string[]
     */
    public function getMethods()
    {
        return $this->methods;
    }
}
