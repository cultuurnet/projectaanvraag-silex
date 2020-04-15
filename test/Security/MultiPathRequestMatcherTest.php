<?php

namespace CultuurNet\UiTIDProvider\Security;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class MultiPathRequestMatcherTest extends TestCase
{
    /**
     * @var RequestMatcherInterface
     */
    private $requestMatcher;

    protected function setUp(): void
    {
        $this->requestMatcher = new MultiPathRequestMatcher([
            '^/some/path',
            '^/some/other/path'
        ]);
    }

    /**
     * @test
     */
    public function it_matches_requests_against_multiple_paths()
    {
        $matchingRequest = new Request();
        $matchingRequest->server->set('REQUEST_URI', '/some/path');
        $matchingRequest->initialize(
            $matchingRequest->query->all(),
            $matchingRequest->request->all(),
            $matchingRequest->attributes->all(),
            $matchingRequest->cookies->all(),
            $matchingRequest->files->all(),
            $matchingRequest->server->all(),
            $matchingRequest->getContent()
        );

        $matches = $this->requestMatcher->matches($matchingRequest);
        $this->assertTrue($matches);
    }

    /**
     * @test
     */
    public function it_does_not_match_a_path_not_in_the_multi_path_configuration()
    {
        $nonMatchingRequest = new Request();
        $nonMatchingRequest->server->set('REQUEST_URI', '/incorrect/path');
        $nonMatchingRequest->initialize(
            $nonMatchingRequest->query->all(),
            $nonMatchingRequest->request->all(),
            $nonMatchingRequest->attributes->all(),
            $nonMatchingRequest->cookies->all(),
            $nonMatchingRequest->files->all(),
            $nonMatchingRequest->server->all(),
            $nonMatchingRequest->getContent()
        );

        $matches = $this->requestMatcher->matches($nonMatchingRequest);
        $this->assertFalse($matches);
    }

    /**
     * @test
     */
    public function it_does_not_match_any_request_when_no_paths_are_provided()
    {
        $requestMatcher = new MultiPathRequestMatcher([]);

        $matches = $requestMatcher->matches(new Request());
        $this->assertFalse($matches);
    }

    /**
     * @test
     */
    public function it_does_match_the_method_when_provided()
    {
        $this->requestMatcher = new MultiPathRequestMatcher(
            [
                '^/some/path',
                '^/some/other/path'
            ],
            [
                'GET',
                'DELETE'
            ]
        );

        $matchingRequest = Request::create('/some/path', 'GET');
        $matches = $this->requestMatcher->matches($matchingRequest);
        $this->assertTrue($matches);


        $nonMatchingRequest = Request::create('/some/other/path', 'GET');
        $matches = $this->requestMatcher->matches($nonMatchingRequest);
        $this->assertFalse($matches);
    }

    /**
     * @test
     */
    public function it_should_match_against_one_of_mutiple_methods_on_the_same_path()
    {
        $matcher = new MultiPathRequestMatcher();
        $matcher = $matcher->withPath(new Path('^/foo/bar', ['DELETE', 'POST']));

        $request = Request::create('/foo/bar', 'POST');
        $match = $matcher->matches($request);
        $this->assertTrue($match);
    }

    /**
     * @test
     */
    public function it_should_not_match_against_a_missing_method_of_multi_method_path()
    {
        $matcher = new MultiPathRequestMatcher();
        $matcher = $matcher->withPath(new Path('^/foo/bar', ['DELETE', 'POST']));

        $request = Request::create('/foo/bar', 'GET');
        $match = $matcher->matches($request);
        $this->assertFalse($match);
    }

    /**
     * @test
     */
    public function it_should_match_against_paths_created_with_from_paths_method()
    {
        $matcher = MultiPathRequestMatcher::fromPaths([
            new Path('^/some/path', ['GET', 'POST']),
            new Path('^/some/other/path', 'DELETE')
        ]);

        $matchingRequest = Request::create('/some/path', 'GET');
        $matches = $matcher->matches($matchingRequest);
        $this->assertTrue($matches);


        $nonMatchingRequest = Request::create('/some/other/path', 'GET');
        $matches = $matcher->matches($nonMatchingRequest);
        $this->assertFalse($matches);
    }
}
