<?php

namespace CultuurNet\ProjectAanvraag\Widget\Controller;

use CultuurNet\ProjectAanvraag\CssStats\CssStatsServiceInterface;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Project\Converter\ProjectConverter;
use CultuurNet\ProjectAanvraag\Voter\ProjectVoter;
use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use CultuurNet\ProjectAanvraag\Widget\Command\DeleteWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Command\RevertWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Command\UpdateWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Command\CreateWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Command\PublishWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Command\UpgradeWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Converter\WidgetPageConverter;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageEntityDeserializer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeDiscovery;
use Doctrine\ODM\MongoDB\DocumentRepository;
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
     * @var WidgetPageConverter
     */
    protected $widgetPageConverter;

    /**
     * @var WidgetPageEntityDeserializer
     */
    protected $widgetPageDeserializer;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * @var CssStatsServiceInterface
     */
    protected $cssStatsService;

    /**
     * WidgetApiController constructor.
     * @param MessageBusSupportingMiddleware $commandBus
     * @param DocumentRepository $widgetPageRepository
     * @param ProjectConverter $projectConverter
     * @param WidgetTypeDiscovery $widgetTypeDiscovery
     * @param WidgetPageEntityDeserializer $widgetPageDeserializer
     * @param WidgetPageConverter $widgetPageConverter
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RendererInterface $renderer
     * @param CssStatsServiceInterface $cssStatsService
     */
    public function __construct(
        MessageBusSupportingMiddleware $commandBus,
        DocumentRepository $widgetPageRepository,
        WidgetTypeDiscovery $widgetTypeDiscovery,
        WidgetPageEntityDeserializer $widgetPageDeserializer,
        WidgetPageConverter $widgetPageConverter,
        AuthorizationCheckerInterface $authorizationChecker,
        RendererInterface $renderer,
        CssStatsServiceInterface $cssStatsService
    ) {
        $this->commandBus = $commandBus;
        $this->widgetPageRepository = $widgetPageRepository;
        $this->widgetTypeDiscovery = $widgetTypeDiscovery;
        $this->widgetPageConverter = $widgetPageConverter;
        $this->widgetPageDeserializer = $widgetPageDeserializer;
        $this->authorizationChecker = $authorizationChecker;
        $this->renderer = $renderer;
        $this->cssStatsService = $cssStatsService;
    }

    /**
     * Return the list of available widget types + default settings.
     */
    public function getWidgetTypes(Request $request)
    {
        $types = [];
        $definitions = $this->widgetTypeDiscovery->getDefinitions();
        foreach ($definitions as $id => $definition) {
            /** @var WidgetType $annotation */
            $annotation = $definition['annotation'];
            $types[$annotation->getId()] = $annotation->getDefaultSettings();

            // Replace base urls in default settings of header / footer.
            if (isset($types[$annotation->getId()]['header']) && !empty($types[$annotation->getId()]['header']['body'])) {
                $types[$annotation->getId()]['header']['body'] = str_replace('{{ base_url }}', $request->getScheme() . '://' . $request->getHost() . $request->getBaseUrl(), $types[$annotation->getId()]['header']['body']);
            }

            if (isset($types[$annotation->getId()]['footer']) && !empty($types[$annotation->getId()]['footer']['body'])) {
                $types[$annotation->getId()]['footer']['body'] = str_replace('{{ base_url }}', $request->getScheme() . '://' . $request->getHost() . $request->getBaseUrl(), $types[$annotation->getId()]['footer']['body']);
            }
        }

        // Return the types + cache the response for 24 hours.
        return JsonResponse::create($types)->setSharedMaxAge(60 * 60 * 24);
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
            ['projectId' => (string) $project->getId()],
            ['created' => 'DESC']
        );

        $widgetPagesList = [];

        /** @var WidgetPageInterface $widgetPage */
        foreach ($widgetPages as $widgetPage) {
            // When there is a draft version, add the draft version, otherwise only add the published version if it is not already included in the array
            if ($widgetPage->isDraft() || empty($widgetPagesList[$widgetPage->getId()])) {
                $widgetPagesList[$widgetPage->getId()] = $widgetPage;
            }
        }

        return JsonResponse::create($widgetPagesList)->setSharedMaxAge(0);
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

        try {
            $draftWidgetPage = $this->widgetPageConverter->convertToDraft($widgetPage->getId());
            $this->commandBus->handle(new UpdateWidgetPage($widgetPage, $draftWidgetPage));
        } catch (NotFoundHttpException $e) {
            $this->commandBus->handle(new CreateWidgetPage($widgetPage));
        }

        $data = [
            'widgetPage' => $widgetPage->jsonSerialize(),
        ];

        if ($request->query->has('render')) {
            $this->renderer->setProject($project);
            if ($widget = $widgetPage->getWidget($request->query->get('render'))) {
                $data['preview'] = $this->renderer->renderWidget($widget, '', $widgetPage->getLanguage());
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
    public function publishWidgetPage(ProjectInterface $project, WidgetPageInterface $widgetPage)
    {

        // Check if user has edit access.
        $this->verifyProjectAccess($project, $widgetPage, ProjectVoter::EDIT);

        // There was no draft, no publish action needed
        if (!$widgetPage->isDraft()) {
            return new JsonResponse();
        }

        $this->commandBus->handle(new PublishWidgetPage($widgetPage));

        return new JsonResponse();
    }


    /**
     * Revert the requested widget page to the published version.
     *
     * @param ProjectInterface $project
     * @param $pageId
     *
     * @return JsonResponse
     *
     */
    public function revertWidgetPage(ProjectInterface $project, WidgetPageInterface $widgetPage)
    {

        // Check if user has edit access.
        $this->verifyProjectAccess($project, $widgetPage, ProjectVoter::EDIT);

        // There was no draft, no revert action needed
        if (!$widgetPage->isDraft()) {
            return new JsonResponse();
        }

        $this->commandBus->handle(new RevertWidgetPage($widgetPage));

        return new JsonResponse();
    }

    /**
     * Upgrade the requested widget page to the latest version.
     *
     * @param ProjectInterface $project
     * @param $pageId
     *
     * @return JsonResponse
     *
     */
    public function upgradeWidgetPage(ProjectInterface $project, WidgetPageInterface $widgetPage)
    {

        // Check if user has edit access.
        $this->verifyProjectAccess($project, $widgetPage, ProjectVoter::EDIT);
        $this->commandBus->handle(new UpgradeWidgetPage($widgetPage));

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
     * Validate if the user has access to given project, for a given widget page.
     * @param ProjectInterface $project
     * @param WidgetPageInterface $widgetPage
     * @param string $access
     */
    protected function verifyProjectAccess(ProjectInterface $project, WidgetPageInterface $widgetPage, $access = ProjectVoter::VIEW)
    {
        if ($project->getId() != $widgetPage->getProjectId()) {
            throw new \InvalidArgumentException('The widget page project id does not match the current project.');
        }

        if (!$this->authorizationChecker->isGranted($access, $project)) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * Get CSS stats for a given URL
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCssStats(Request $request)
    {
        if (!$request->query->has('url') || !filter_var($request->query->get('url'), FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Provide a valid URL to scrape.');
        }

        $response = new JsonResponse($this->cssStatsService->getCssStatsFromUrl($request->query->get('url')));
        $response->setTtl(86400);

        return $response;
    }
}
