<?php

namespace CultuurNet\ProjectAanvraag\Widget\Controller;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use CultuurNet\ProjectAanvraag\Widget\Command\UpdateWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Command\CreateWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Command\PublishWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Renderer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageEntityDeserializer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeDiscovery;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Satooshi\Bundle\CoverallsV1Bundle\Entity\Exception\RequirementsNotSatisfiedException;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * Get a widget page.
     * @param WidgetPageInterface $widgetPage
     */
    public function getWidgetPage(ProjectInterface $project, WidgetPageInterface $widgetPage)
    {
        // todo: validation on project id + validation on project edit access.
        $this->verifyProjectId($project->getId(), $widgetPage->getProjectId());

        //if (!$this->authorizationChecker->isGranted('edit', $project)) {
        //    throw new AccessDeniedHttpException();
        //}

        return new JsonResponse($widgetPage);
    }

    /**
     * Update or create a posted widget page.
     */
    public function updateWidgetPage(ProjectInterface $project, Request $request)
    {
        //if (!$this->authorizationChecker->isGranted('edit', $project)) {
        //    throw new AccessDeniedHttpException();
        //}

        $widgetPage = $this->widgetPageDeserializer->deserialize($request->getContent());

        // Check if projectID of the request is the same as the projectID in the url
        if ($widgetPage->getProjectId() !== $project->getId()) {
            throw new RequirementsNotSatisfiedException('ProjectIds do not match');
        }

        //Load widget page if an ID was provided
        $existingWidgetPages = [];
        if ($widgetPage->getId()) {
            $existingWidgetPages = $this->loadExistingWidgetPages($widgetPage->getId());
        }

        if (count($existingWidgetPages) > 0) {
            $this->verifyProjectId($existingWidgetPages[0]->getProjectId(), $widgetPage->getProjectId());

            // Search for a draft version.
            $draftWidgetPage = $this->filterOutDraftPage($existingWidgetPages);

            // If no draft was found, use the published one as source.
            if (empty($draftWidgetPage)) {
                $draftWidgetPage = $existingWidgetPages[0];
            }

            $this->commandBus->handle(new UpdateWidgetPage($widgetPage, $draftWidgetPage));
        } else {
            $this->commandBus->handle(new CreateWidgetPage($widgetPage));
        }

        $data = [
            'widgetPage' => $widgetPage->jsonSerialize(),
        ];

        $renderer = new Renderer();
        if ($request->query->has('render')) {
            if ($widget = $widgetPage->getWidget($request->query->get('render'))) {
                $data['preview'] = $renderer->renderWidget($widget);
            } else {
                $data['preview'] = '';
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Publish the requested widget page.
     * @param ProjectInterface $project
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function publishWidgetPage(ProjectInterface $project, $pageId)
    {
       // if (!$this->authorizationChecker->isGranted('edit', $project)) {
       //     throw new AccessDeniedHttpException();
       // }

        // Load the widget page.
        $existingWidgetPages = $this->loadExistingWidgetPages($pageId);

        if (!empty($existingWidgetPages)) {
            // Validate if loaded project has the same project id
            $this->verifyProjectId($existingWidgetPages[0]->getProjectId(), $project->getId());

            // Search for a draft version.
            $draftWidgetPage = $this->filterOutDraftPage($existingWidgetPages);

            if (empty($draftWidgetPage)) {
                return new JsonResponse();
            }

            $this->commandBus->handle(new PublishWidgetPage($draftWidgetPage));
        } else {
            throw new RequirementsNotSatisfiedException('No Widget Page was found');
        }

        return new JsonResponse();
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


    /**
     * Validate if loaded project has the same project id
     *
     * @param $existingWidgetPageId
     * @param $newWidgetPageId
     *
     * @return bool
     */
    protected function verifyProjectId($existingWidgetPageId, $newWidgetPageId)
    {
        if ($existingWidgetPageId != $newWidgetPageId) {
            throw new RequirementsNotSatisfiedException('Saved ProjectId do not match the requested one');
        }
    }

    /**
     * Load all the existing WidgetPages for a given ID
     * @param Integer $pageId
     *
     * @return array
     */
    protected function loadExistingWidgetPages($pageId)
    {
        return $this->widgetPageRepository->findBy(
            [
                'id' => $pageId,
            ]
        );
    }

    /**
     * Filter out the draft version out of a group of widget pages
     * @param array $widgetPages
     *
     * @return WidgetPageInterface|null
     */
    protected function filterOutDraftPage(array $widgetPages)
    {

        /** @var WidgetPageInterface $page */
        foreach ($widgetPages as $page) {
            if ($page->isDraft()) {
                return $page;
                break;
            }
        }
    }
}
