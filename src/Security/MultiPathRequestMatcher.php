<?php

namespace CultuurNet\UiTIDProvider\Security;

use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class MultiPathRequestMatcher implements RequestMatcherInterface
{
    /**
     * @var Path[]
     */
    protected $paths;

    /**
     * MultiPathRequestMatcher constructor.
     * @param array $pathPatterns
     * @param array $methods
     */
    public function __construct(array $pathPatterns = [], array $methods = [])
    {
        $paths = [];

        foreach ($pathPatterns as $index => $pathPattern) {
            $paths[] = new Path(
                $pathPattern,
                array_key_exists($index, $methods) ? [$methods[$index]] : []
            );
        }

        $this->paths = $paths;
    }

    /**
     * Creates a new request matcher with the addition of the provided path.
     *
     * @param Path $path
     * @return MultiPathRequestMatcher
     */
    public function withPath(Path $path)
    {
        $matcher = clone $this;
        $matcher->paths[] = $path;
        return $matcher;
    }

    /**
     * @param array $paths
     * @return MultiPathRequestMatcher
     */
    public function withPaths(array $paths)
    {
        $matcher = clone $this;
        $matcher->paths = array_merge($matcher->paths, $paths);
        return $matcher;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function matches(Request $request)
    {
        $match = false;

        if (is_array($this->paths)) {
            $i = 0;
            $pathCount = count($this->paths);

            while ($i < $pathCount && !$match) {
                $match = !!preg_match('{'.$this->paths[$i]->getPattern().'}', rawurldecode($request->getPathInfo()));

                $allowedMethods = $this->paths[$i]->getMethods();

                // if we have a matching path and we are checking for methods
                // make sure the method matches as well
                if ($match && !empty($allowedMethods)) {
                    $requestMethod = $request->getMethod();
                    $matchingMethods = array_filter($allowedMethods, function ($method) use ($requestMethod) {
                        return $requestMethod === $method;
                    });

                    $match = count($matchingMethods) > 0;
                }

                $i++;
            }
        }

        return $match;
    }

    /**
     * @param Path[] $paths
     * @return MultiPathRequestMatcher
     */
    public static function fromPaths(array $paths)
    {
        $matcher = new self();
        return $matcher->withPaths($paths);
    }
}
