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

    /**
     * {@inheritdoc}
     */
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
            $rows[] = $app['widget_layout_manager']->createInstance($row['type']);
        }

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
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
                $return[] = $app[\'widget_layout_manager\']->createInstance($row[\'type\'], $row);
            }
        ';
    }

    /**
     * {@inheritdoc}
     */
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
