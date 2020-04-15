<?php

namespace CultuurNet\UiTIDProvider\Security;

use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
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
     * @var Path
     */
    private $path;

    protected function setUp(): void
    {
        $this->pattern = 'pattern';
        $this->methods = ['GET', 'POST'];

        $this->path = new Path($this->pattern, $this->methods);
    }

    /**
     * @test
     */
    public function it_stores_a_pattern()
    {
        $this->assertEquals($this->pattern, $this->path->getPattern());
    }

    /**
     * @test
     */
    public function it_stores_methods()
    {
        $this->assertEquals($this->methods, $this->path->getMethods());
    }

    /**
     * @test
     */
    public function it_converts_single_method_to_array()
    {
        $path = new Path('pattern', 'GET');

        $this->assertEquals(['GET'], $path->getMethods());
    }
}
