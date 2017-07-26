<?php

namespace CultuurNet\ProjectAanvraag\Widget\Controller;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\Renderer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageEntityDeserializer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeDiscovery;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\DocumentRepository;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializerBuilder;
use SimpleBus\JMSSerializerBridge\SerializerMetadata;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Provides the controller for main widget builder api requests.
 */
class WidgetApiController
{

    /**
     * @var WidgetTypeDiscovery
     */
    protected $widgetTypeDiscovery;

    /**
     * @var DocumentRepository
     */
    protected $widgetPageRepository;

    /**
     * @var WidgetPageEntityDeserializer
     */
    protected $widgetPageDeserializer;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * WidgetApiController constructor.
     * @param DocumentRepository $widgetPageRepository
     * @param WidgetPluginManager $widgetTypePluginManager
     * @param WidgetPageEntityDeserializer $widgetPageDeserializer
     */
    public function __construct(DocumentRepository $widgetPageRepository, WidgetTypeDiscovery $widgetTypeDiscovery, WidgetPageEntityDeserializer $widgetPageDeserializer, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->widgetPageRepository = $widgetPageRepository;
        $this->widgetTypeDiscovery = $widgetTypeDiscovery;
        $this->widgetPageDeserializer = $widgetPageDeserializer;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Return the list of available widget types + default settings.
     */
    public function getWidgetTypes()
    {
        $types = [];
        $definitions = $this->widgetTypeDiscovery->getDefinitions();
        foreach ($definitions as $id => $definition) {
            /** @var WidgetType $annotation */
            $annotation = $definition['annotation'];
            $types[$annotation->getId()] = $annotation->getDefaultSettings();
        }

        return new JsonResponse($types);
    }

    /**
     * Update a posted widget page.
     */
    public function updateWidgetPage($project, Request $request)
    {

        if (!$this->authorizationChecker->isGranted('edit', $project)) {
            throw new AccessDeniedHttpException();
        }

        $test = $this->widgetPageDeserializer->deserialize($request->getContent());

        return new JsonResponse($test->jsonSerialize());
    }

    /**
     * temp test
     */
    public function test(Request $request)
    {

        if ($request->getMethod() == 'GET') {
            $json = file_get_contents(__DIR__ . '/../../../test/Widget/data/page.json');
        }
        else {
            $json = $request->getContent();
        }

        $page = $this->widgetPageDeserializer->deserialize($json);

        $data = [
            'page' => $page->jsonSerialize(),
        ];

        $renderer = new Renderer();
        if ($request->query->has('render')) {
            if ($widget = $page->getWidget($request->query->get('render'))) {
                $data['preview'] = $renderer->renderWidget($widget);
            }
            else {
                $data['preview'] = '';
            }
        }

        return new JsonResponse($data);
    }
}
