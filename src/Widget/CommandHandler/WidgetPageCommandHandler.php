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
 * Provides an abstract class for crud actions on widget pages.
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
     * Make sure every facet widget is targetting a search results widget
     * If a facet widget does not target a search widget. It should target the first search result on the page.
     *
     * @param WidgetPageEntity $widgetPage
     */
    public function determineFacetTargeting(WidgetPageEntity $widgetPage)
    {
        $widgetResultsCount = 0;
        $resultIdToTarget = 0;
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
        if (!empty($facetWidgets) && $resultIdToTarget) {
            /** @var Facets $facetWidget */
            foreach ($facetWidgets as $facetWidget) {
                if ($facetWidget->getTargetedSearchResultsWidgetId() === '') {
                    $facetWidget->setTargettedSearchResultsWidgetId($resultIdToTarget);
                }
            }
        }
    }
}
