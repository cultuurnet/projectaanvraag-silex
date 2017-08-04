<?php

namespace CultuurNet\ProjectAanvraag\Widget\Controller;

use CultuurNet\ProjectAanvraag\Core\Exception\ValidationException;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use CultuurNet\ProjectAanvraag\Widget\Command\UpdateWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Command\CreateWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Command\PublishWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\Renderer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageEntityDeserializer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeDiscovery;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\DocumentRepository;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializerBuilder;
use Satooshi\Bundle\CoverallsV1Bundle\Entity\Exception\RequirementsNotSatisfiedException;
use SimpleBus\JMSSerializerBridge\SerializerMetadata;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
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
     * @var MessageBusSupportingMiddleware
     */
    protected $commandBus;

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
     *
     * @param DocumentRepository $widgetPageRepository
     * @param WidgetPluginManager $widgetTypePluginManager
     * @param WidgetPageEntityDeserializer $widgetPageDeserializer
     */
    public function __construct(MessageBusSupportingMiddleware $commandBus, DocumentRepository $widgetPageRepository, WidgetTypeDiscovery $widgetTypeDiscovery, WidgetPageEntityDeserializer $widgetPageDeserializer, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->commandBus = $commandBus;
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
    public function updateWidgetPage(ProjectInterface $project, Request $request)
    {
//        if (!$this->authorizationChecker->isGranted('edit', $project)) {
//            throw new AccessDeniedHttpException();
//        }

        $widgetPage = $this->widgetPageDeserializer->deserialize($request->getContent());

        // Check if projectID of the request is the same as the projectID in the url
        if ($widgetPage->getProjectId() !== $project->getId()) {
            throw new RequirementsNotSatisfiedException('ProjectIds do not match');
        }

        //Load widget page if an ID was provided
        $existingWidgetPages = [];
        if ($widgetPage->getId()) {
            $existingWidgetPages = $this->widgetPageRepository->findBy(
                [
                'id' => $widgetPage->getId(),
                ]
            );
        }

        if (count($existingWidgetPages) > 0) {

            // Search for a draft version.
            $existingWidgetPage = null;
            /** @var WidgetPageInterface $page */
            foreach ($existingWidgetPages as $page) {
                if ($page->isDraft()) {
                    $existingWidgetPage = $page;
                    break;
                }
            }

            // If no draft was found, use the published one as source.
            if (empty($existingWidgetPage)) {
                $existingWidgetPage = $existingWidgetPages[0];
            }

            // Validate if loaded project has the same project id
            if ($existingWidgetPages[0]->getProjectId() != $widgetPage->getProjectId()) {
                throw new RequirementsNotSatisfiedException('Saved ProjectId do not match the requested one');
            }

            $this->commandBus->handle(new UpdateWidgetPage($widgetPage, $existingWidgetPage));

        } else {
            $this->commandBus->handle(new CreateWidgetPage($widgetPage));
        }

        return new JsonResponse($widgetPage->jsonSerialize());
    }

    /**
     * temp test
     */
    public function test(Request $request)
    {

        if ($request->getMethod() == 'GET') {
            $json = file_get_contents(__DIR__ . '/../../../test/Widget/data/page.json');
        } else {
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
            } else {
                $data['preview'] = '';
            }
        }

        return new JsonResponse($data);
    }
}
