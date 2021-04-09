<?php

namespace CultuurNet\ProjectAanvraag\Widget\ODM\Types;

use CultuurNet\ProjectAanvraag\Widget\LayoutInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetFactory;
use Doctrine\ODM\MongoDB\Types\Type;

/**
 * ODM datetype for page rows.
 */
class PageRows extends Type
{

    public function convertToPHPValue($value)
    {
        // Note: this function is only called when your custom type is used
        // as an identifier. For other cases, closureToPHP() will be called.

        global $app;
        if (!is_array($value)) {
            return;
        }

        $rows = [];
        foreach ($value as $row) {
            if (isset($row["regions"]["content"]["widgets"])) {
                // Convert old default_image setting value to new format.
                // In the past it was just a boolean, but now it's supposed to be an object
                // with `enabled` and `type`.
                foreach ($row["regions"]["content"]["widgets"] as &$widget) {
                    if (isset($widget["settings"]["items"]["image"]["default_image"]) && is_bool($widget["settings"]["items"]["image"]["default_image"])) {
                        $widget["settings"]["items"]["image"]["default_image"] = [
                            "enabled" => $widget["settings"]["items"]["image"]["default_image"],
                            "type" => "uit",
                        ];
                    }

                    if (isset($widget["settings"]["detail_page"]["image"]["default_image"]) && is_bool($widget["settings"]["detail_page"]["image"]["default_image"])) {
                        $widget["settings"]["detail_page"]["image"]["default_image"] = [
                            "enabled" => $widget["settings"]["detail_page"]["image"]["default_image"],
                            "type" => "uit",
                        ];
                    }
                }
            }
            
            $rows[] = $app['widget_layout_manager']->createInstance($row['type']);
        }

        return $rows;
    }

    public function closureToPHP()
    {
        // Return the string body of a PHP closure that will receive $value
        // and store the result of a conversion in a $return variable
        return '
            global $app;
            if (!is_array($value)) {
                return;
            }
    
            $return = [];
            foreach ($value as $row) {
                if (isset($row["regions"]["content"]["widgets"])) {
                    foreach ($row["regions"]["content"]["widgets"] as &$widget) {
                        if (isset($widget["settings"]["items"]["image"]["default_image"]) && is_bool($widget["settings"]["items"]["image"]["default_image"])) {
                            $widget["settings"]["items"]["image"]["default_image"] = [
                                "enabled" => $widget["settings"]["items"]["image"]["default_image"],
                                "type" => "uit",
                            ];
                        }
    
                        if (isset($widget["settings"]["detail_page"]["image"]["default_image"]) && is_bool($widget["settings"]["detail_page"]["image"]["default_image"])) {
                            $widget["settings"]["detail_page"]["image"]["default_image"] = [
                                "enabled" => $widget["settings"]["detail_page"]["image"]["default_image"],
                                "type" => "uit",
                            ];
                        }
                    }
                }

                $return[] = $app["widget_layout_manager"]->createInstance($row["type"], $row);
            }
        ';
    }

    public function convertToDatabaseValue($value)
    {
        $dbValue = [];

        if (is_array($value)) {
            /** @var LayoutInterface $row */
            foreach ($value as $row) {
                $dbValue[] = $row->jsonSerialize();
            }
        }

        return $dbValue;
    }
}
