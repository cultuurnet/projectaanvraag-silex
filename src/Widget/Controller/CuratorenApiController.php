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
class CuratorenApiController
{

    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $commandBus;

    /**
     * WidgetApiController constructor.
     * @param MessageBusSupportingMiddleware $commandBus
     */
    public function __construct(
        MessageBusSupportingMiddleware $commandBus,
    ) {
        $this->commandBus = $commandBus;
    }

    /**
     * Get CSS stats for a given URL
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        if (!$request->query->has('url') || !filter_var($request->query->get('url'), FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Provide a valid URL to scrape.');
        }

        $response = new JsonResponse($this->cssStatsService->getCssStatsFromUrl($request->query->get('url')));
        $response->setTtl(86400);

        return $response;
    }
}
