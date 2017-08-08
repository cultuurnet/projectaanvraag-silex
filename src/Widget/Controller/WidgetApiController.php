<?php

namespace CultuurNet\ProjectAanvraag\Widget\Controller;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Voter\ProjectVoter;
use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use CultuurNet\ProjectAanvraag\Widget\Command\DeleteWidgetPage;
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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     *
     * @param ProjectInterface $project
     * @param WidgetPageInterface $widgetPage
     *
     * @return JsonResponse
     */
    public function getWidgetPage(ProjectInterface $project, WidgetPageInterface $widgetPage)
    {
        $this->verifyProjectAccess($project, $widgetPage);
        return new JsonResponse($widgetPage);
    }

    /**
     * Get the list of widget pages for given project.
     */
    public function getWidgetPages(ProjectInterface $project)
    {

        if (!$this->authorizationChecker->isGranted(ProjectVoter::VIEW, $project)) {
            throw new AccessDeniedHttpException();
        }

        $widgetPages = $this->widgetPageRepository->findBy(
            [
                'projectId' => (string) $project->getId(),
            ],
            ['title' => 'ASC']
        );

        $widgetPagesList = array();
        /**
         * @var  $key
         * @var WidgetPageInterface $widgetPage
         */
        foreach ($widgetPages as $key => $widgetPage)
        {
            // When there is a draft version, add the draft version, otherwise only add the published version if it is not already included in the array
            if($widgetPage->isDraft() || !isset($key, $widgetPagesList))
            {
                $widgetPagesList[$key] = $widgetPage;
            }
        }

        return new JsonResponse($widgetPagesList);
    }

    /**
     * Update or create a posted widget page.
     *
     * @param ProjectInterface $project
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateWidgetPage(ProjectInterface $project, Request $request)
    {

        $widgetPage = $this->widgetPageDeserializer->deserialize($request->getContent());

        // Check if user has edit access.
        $this->verifyProjectAccess($project, $widgetPage, ProjectVoter::EDIT);

        // Load widget page if an ID was provided
        $existingWidgetPages = [];
        if ($widgetPage->getId()) {
            $existingWidgetPages = $this->loadExistingWidgetPages($widgetPage->getId(), $project->getId());
        }

        if (count($existingWidgetPages) > 0) {
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
     *
     * @param ProjectInterface $project
     * @param $pageId
     *
     * @return JsonResponse
     *
     */
    public function publishWidgetPage(ProjectInterface $project, $pageId)
    {

        // Load the widget page.
        $existingWidgetPages = $this->loadExistingWidgetPages($pageId, $project->getId());

        if (empty($existingWidgetPages)) {
            throw new NotFoundHttpException('The given widget page does not exist for given project.');
        }

        if (!empty($existingWidgetPages)) {
            // Check if user has edit access.
            $this->verifyProjectAccess($project, $existingWidgetPages[0], ProjectVoter::EDIT);

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
     * @param ProjectInterface $project
     * @param WidgetPageInterface $widgetPage
     *
     * @return JsonResponse
     */
    public function deleteWidgetPage(ProjectInterface $project, WidgetPageInterface $widgetPage)
    {
        $this->verifyProjectAccess($project, $widgetPage, ProjectVoter::EDIT);
        $this->commandBus->handle(new DeleteWidgetPage($widgetPage));

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
     * Validate if the user has access to given project, for a given widget page.
     * @param ProjectInterface $project
     * @param WidgetPageInterface $widgetPage
     * @param string $access
     */
    protected function verifyProjectAccess(ProjectInterface $project, WidgetPageInterface $widgetPage, $access = ProjectVoter::VIEW)
    {
        if ($project->getId() != $widgetPage->getProjectId()) {
            throw new RequirementsNotSatisfiedException('The widget page project id does not match the current project.');
        }

        if (!$this->authorizationChecker->isGranted($access, $project)) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * Load all the existing WidgetPages for a given ID
     * @param string $pageId
     * @param integer $projectId
     * @return array
     */
    protected function loadExistingWidgetPages($pageId, $projectId)
    {
        return $this->widgetPageRepository->findBy(
            [
                'id' => $pageId,
                'project_id' => $projectId,
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
