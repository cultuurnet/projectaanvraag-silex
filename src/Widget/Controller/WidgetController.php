<?php

namespace CultuurNet\ProjectAanvraag\Widget\Controller;

use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetRowEntity;
use CultuurNet\ProjectAanvraag\Widget\JavascriptResponse;
use CultuurNet\ProjectAanvraag\Widget\LayoutDiscovery;
use CultuurNet\ProjectAanvraag\Widget\LayoutManager;
use CultuurNet\ProjectAanvraag\Widget\Renderer;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeDiscovery;
use CultuurNet\SearchV3\Hydrator\Event;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\SearchQueryInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializerBuilder;
use ML\JsonLD\JsonLD;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;
use SimpleBus\JMSSerializerBridge\JMSSerializerObjectSerializer;
use SimpleBus\JMSSerializerBridge\SerializerMetadata;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides a controller to render widget pages and widgets.
 */
class WidgetController
{

    /**
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * @var DocumentRepository
     */
    protected $widgetRepository;

    /**
     * WidgetController constructor.
     *
     * @param RendererInterface $renderer
     * @param DocumentRepository $widgetRepository
     * @param Connection $db
     */
    public function __construct(RendererInterface $renderer, DocumentRepository $widgetRepository, Connection $db)
    {
        $this->renderer = $renderer;
        $this->widgetRepository = $widgetRepository;

/*        $json = file_get_contents(__DIR__ . '/../../../test/Widget/data/page.json');
        $doc = json_decode($json, true);
        $collection->insert($doc);
        die();*/

/*        $layoutDiscovery = new LayoutDiscovery();
        $layoutDiscovery->register(__DIR__ . '/../WidgetLayout', 'CultuurNet\ProjectAanvraag\Widget\WidgetLayout');

        $typeDiscovery = new WidgetTypeDiscovery();
        $typeDiscovery->register(__DIR__ . '/../WidgetType', 'CultuurNet\ProjectAanvraag\Widget\WidgetType');

        $layoutManager = new WidgetPluginManager($layoutDiscovery);
        $test = $layoutManager->createInstance('one-col');

        $widgetTypeManager = new WidgetPluginManager($typeDiscovery);
        $test2 = $widgetTypeManager->createInstance('search-form');
print_r($test);
print_r($test2);
        die();*/

        /*$results = $collection->find();
        while ($results->hasNext()) {
            $document = $results->getNext();
            //print '<pre>' . print_r($document, true) . '</pre>';
        }*/
    }

    public function renderPage()
    {

      $jmsSerializer = SerializerBuilder::create()
        ->addMetadataDir(SerializerMetadata::directory(), SerializerMetadata::namespacePrefix())
        ->setAnnotationReader(new AnnotationReader())
          ->setPropertyNamingStrategy(new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy()))
        ->build();
      $objectSerializer = new JMSSerializerObjectSerializer($jmsSerializer, 'json');

        $query = new SearchQuery();
        $query->addSort('availableTo', SearchQueryInterface::SORT_DIRECTION_ASC);

        $client = new \Guzzle\Http\Client('https://search-acc.uitdatabank.be');
        $searchClient = new SearchClient($client);
        $result = $searchClient->searchOffers($query);
        $jsonld = $result->getBody(true);
        $json = json_decode($jsonld);

        $event = $json->member[0];

        $test2 = $objectSerializer->deserialize(json_encode($event), Event::class);

        /*$collection = $this->db->selectCollection('widgets', 'WidgetPage');

        $results = $collection->find();
        while ($results->hasNext()) {
            $document = $results->getNext();
            //print '<pre>' . print_r($document, true) . '</pre>';
        }*/

        /** @var WidgetPageEntity $test */
        $test = $this->widgetRepository->findOneBy(
            [
            'id' => '593fb8455722ed4df9064183',
            ]
        );

        $javascriptResponse = new JavascriptResponse($this->renderer, $this->renderer->renderPage($test));

        return new Response('<html><script type="text/javascript">' . $javascriptResponse->getContent() . '</script></html>');
    }

    public function renderWidget()
    {
    }
}
