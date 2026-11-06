<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Curatoren\CuratorenClient;
use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\Twig\TwigPreprocessor;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\Serializer\SerializerInterface;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SearchResultsTest extends TestCase
{
    /**
     * Test if the page query parameter is converted to a valid start offset.
     *
     * @dataProvider pageProvider
     */
    public function testPageQueryParameterSetsStart($page, ?int $expectedStart)
    {
        $start = null;

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('request')->willReturnCallback(
            function (string $method, string $uri, array $options) use (&$start) {
                $start = $options['query']['start'] ?? null;
                throw new \RuntimeException('Stop rendering after the search query is built.');
            }
        );
        $searchClient = new SearchClient($httpClient, $this->createMock(SerializerInterface::class));

        try {
            $this->createSearchResults(['page' => $page], $searchClient)->render();
        } catch (\RuntimeException $e) {
        }

        $this->assertSame($expectedStart, $start);
    }

    public function pageProvider(): array
    {
        return [
            'numeric page' => ['2', 20],
            'first page' => ['0', 0],
            'page with trailing characters' => ['2abc', 20],
            'non-numeric page' => ['abc', 0],
            'negative page' => ['-3', 0],
        ];
    }

    private function createSearchResults(array $params, SearchClient $searchClient): SearchResults
    {
        $annotation = new WidgetType();
        $annotation->id = 'search-results';

        $requestStack = new RequestStack();
        $requestStack->push(new Request(['search-result' => [0 => $params]]));

        $searchResults = new SearchResults(
            ['annotation' => $annotation],
            [
                'id' => '1',
                'settings' => [
                    'general' => ['items' => 10],
                    'header' => [],
                    'footer' => [],
                    'items' => [],
                ],
            ],
            false,
            $this->createMock(\Twig_Environment::class),
            $this->createMock(TwigPreprocessor::class),
            $this->createMock(RendererInterface::class),
            $searchClient,
            $this->createMock(CuratorenClient::class),
            $requestStack,
            $this->createMock(MessageBusSupportingMiddleware::class)
        );
        $searchResults->setIndex(0);

        return $searchResults;
    }
}
