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
     * DeleteWidgetPageCommandHandler constructor.
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

    public function determineFacetTargeting(WidgetPageEntity $widgetPage) {
        // Check for facet widgets.
        $widgetResultsCount = 0;
        $resultIdToTarget = false;
        $facetWidgetIds = [];

        $rows = $widgetPage->getRows();

        /** @var LayoutInterface $row */
        foreach ($rows as $index => $row) {
            $widgets = $row->getWidgets();
            foreach ($widgets as $id => $widget) {
                if ($widget instanceof SearchResults && !$widgetResultsCount) {
                    $widgetResultsCount++;
                    $resultIdToTarget = $id;
                }
                if ($widget instanceof Facets && $widgetResultsCount === 1) {
                    // Use row index as key.
                    $facetWidgetIds[$index] = $id;
                }
            }
        }

        // Check facet widgets and target first search results if there's no targeting.
        if (!empty($facetWidgetIds)) {
            /** @var Facets $facetWidget */
            foreach ($facetWidgetIds as $rowId => $facetWidgetId) {
                $row = $rows[$rowId];
                $facetWidget = $row->getWidget($facetWidgetId);
                if ($facetWidget->getTargettedSearchResultsWidgetId() === '' && $resultIdToTarget) {
                    // Set targeting.
                    $facetWidget->setTargettedSearchResultsWidgetId($resultIdToTarget);
                    // Update widget page.
                    $rows[$rowId]->updateWidget($facetWidgetId, $facetWidget);
                    $widgetPage->setRows($rows);
                }
            }
        }

        return $widgetPage;
    }
}
