<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\User\UserInterface;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\LayoutInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetType\Facets;
use CultuurNet\ProjectAanvraag\Widget\WidgetType\SearchResults;
use Doctrine\ODM\MongoDB\DocumentManager;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

/**
 * Provides a command handler to create a new widget page.
 */
abstract class WidgetPageCommandHandler
{
    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * WidgetPageCommandHandler constructor.
     *
     * @param MessageBusSupportingMiddleware $eventBus
     * @param DocumentManager $documentManager
     * @param UserInterface $user
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, DocumentManager $documentManager, UserInterface $user)
    {
        $this->eventBus = $eventBus;
        $this->documentManager = $documentManager;
        $this->user = $user;
    }

    /**
     * Fill in empty search results targeting settings for facet widgets.
     *
     * @param WidgetPageEntity $widgetPage
     */
    public function determineFacetTargeting(WidgetPageEntity $widgetPage)
    {
        $widgetResultsCount = 0;
        $resultIdToTarget = false;
        $facetWidgets = [];
        $rows = $widgetPage->getRows();

        /** @var LayoutInterface $row */
        foreach ($rows as $row) {
            $widgets = $row->getWidgets();
            foreach ($widgets as $id => $widget) {
                if ($widget instanceof SearchResults && !$widgetResultsCount) {
                    $widgetResultsCount++;
                    $resultIdToTarget = $id;
                }
                if ($widget instanceof Facets && $widgetResultsCount === 1) {
                    $facetWidgets[] = $widget;
                }
            }
        }

        // Check facet widgets and target first search results if there's no target set.
        if (!empty($facetWidgets)) {
            /** @var Facets $facetWidget */
            foreach ($facetWidgets as $facetWidget) {
                if ($facetWidget->getTargettedSearchResultsWidgetId() === '' && $resultIdToTarget) {
                    $facetWidget->setTargettedSearchResultsWidgetId($resultIdToTarget);
                }
            }
        }
    }
}
