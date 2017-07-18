<?php

namespace CultuurNet\ProjectAanvraag\Widget\Controller;
use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageEntityDeserializer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\DocumentRepository;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializerBuilder;
use SimpleBus\JMSSerializerBridge\SerializerMetadata;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides the controller for main widget builder api requests.
 */
class WidgetApiController
{

    /**
     * @var WidgetPluginManager
     */
    protected $widgetTypePluginManager;

    /**
     * @var DocumentRepository
     */
    protected $widgetPageRepository;

    /**
     * @var WidgetPageEntityDeserializer
     */
    protected $widgetPageDeserializer;

    /**
     * WidgetApiController constructor.
     * @param DocumentRepository $widgetPageRepository
     * @param WidgetPluginManager $widgetTypePluginManager
     * @param WidgetPageEntityDeserializer $widgetPageDeserializer
     */
    public function __construct(DocumentRepository $widgetPageRepository, WidgetPluginManager $widgetTypePluginManager, WidgetPageEntityDeserializer $widgetPageDeserializer)
    {
        $this->widgetPageRepository = $widgetPageRepository;
        $this->widgetTypePluginManager = $widgetTypePluginManager;
        $this->widgetPageDeserializer = $widgetPageDeserializer;
    }

    /**
     * Return the list of available widget types + default settings.
     */
    public function getWidgetTypes()
    {
        $types = [];
        $definitions = $this->widgetTypePluginManager->getDefinitions();
        foreach ($definitions as $id => $definition) {
            /** @var WidgetType $annotation */
            $annotation = $definition['annotation'];
            $types[$annotation->getId()] = $annotation->getDefaultSettings();
        }

        return new JsonResponse($types);
    }

    /**
     * test json to ODM
     */
    public function test()
    {

        $json = file_get_contents(__DIR__ . '/../../../test/Widget/data/page.json');

        $test = $this->widgetPageDeserializer->deserialize($json);

        return new JsonResponse($test->jsonSerialize());
    }
}
