<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;

/**
 * Provides a deserializer for widget pages.
 */
class WidgetPageEntityDeserializer
{

    /**
     * @var WidgetPluginManager
     */
    protected $widgetLayoutManager;

    /**
     * @var WidgetPluginManager
     */
    protected $widgetTypeManager;

    /**
     * WidgetPageEntityDeserializer constructor.
     * @param WidgetPluginManager $widgetLayoutManager
     * @param WidgetPluginManager $widgetTypeManager
     */
    public function __construct(WidgetPluginManager $widgetLayoutManager, WidgetPluginManager $widgetTypeManager)
    {
        $this->widgetLayoutManager = $widgetLayoutManager;
        $this->widgetTypeManager = $widgetTypeManager;
    }

    /**
     * Deserialize a given JSON to a valid widget page entity.
     * @param $json
     */
    public function deserialize($json)
    {
        $jsonObject = json_decode($json, true);

        $widgetPageEntity = new WidgetPageEntity();

        if (isset($jsonObject['version'])) {
            $widgetPageEntity->setVersion($jsonObject['version']);
        }

        if (isset($jsonObject['title'])) {
            $widgetPageEntity->setTitle($jsonObject['title']);
        }

        if (isset($jsonObject['id'])) {
            $widgetPageEntity->setId($jsonObject['id']);
        }

        if (isset($jsonObject['project_id'])) {
            $widgetPageEntity->setProjectId((int) $jsonObject['project_id']);
        }

        if (isset($jsonObject['created_by'])) {
            $widgetPageEntity->setCreatedBy($jsonObject['created_by']);
        }

        if (isset($jsonObject['last_updated_by'])) {
            $widgetPageEntity->setLastUpdatedBy($jsonObject['last_updated_by']);
        }

        if (isset($jsonObject['created'])) {
            $widgetPageEntity->setCreated($jsonObject['created']);
        }

        if (isset($jsonObject['last_updated'])) {
            $widgetPageEntity->setLastUpdated($jsonObject['last_updated']);
        }

        if (isset($jsonObject['mobile'])) {
            $widgetPageEntity->setMobile($jsonObject['mobile']);
        }

        if (isset($jsonObject['jquery'])) {
            $widgetPageEntity->setJquery($jsonObject['jquery']);
        }

        $rows = [];
        $widgetIndex = -1;
        if (isset($jsonObject['rows']) && is_array($jsonObject['rows'])) {
            foreach ($jsonObject['rows'] as $row) {
                $row['lastIndex'] = $widgetIndex;
                $layout = $this->widgetLayoutManager->createInstance($row['type'], $row, true);
                $widgetIndex = $layout->getLastWidgetIndex();
                $rows[] = $layout;
            }
        }

        $widgetPageEntity->setRows($rows);

        if (isset($jsonObject['css'])) {
            $widgetPageEntity->setCss($jsonObject['css']);
        }

        if (isset($jsonObject['selectedTheme'])) {
            $widgetPageEntity->setSelectedTheme($jsonObject['selectedTheme']);
        }

        return $widgetPageEntity;
    }
}
